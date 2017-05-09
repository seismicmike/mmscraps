<?php

/**
 * @file
 * Contains \Drupal\tour_ui\TourFormController.
 */

namespace Drupal\tour_ui;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityFormController;
use \Drupal\Core\Language\Language;

/**
 * Form controller for the tour entity edit forms.
 */
class TourFormController extends EntityFormController {

  /**
   * Overrides \Drupal\Core\Entity\EntityFormController::form().
   */
  public function form(array $form, array &$form_state) {
    $tour = $this->entity;
    $form = parent::form($form, $form_state);
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => t('Tour name'),
      '#required' => TRUE,
      '#default_value' => $tour->label(),
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#machine_name' => array(
        'exists' => '_tour_load',
        'replace_pattern' => '[^a-z0-9-]+',
        'replace' => '-',
      ),
      '#default_value' => $tour->id(),
      '#disabled' => !$tour->isNew(),
    );

    //TODO: language() is deprecated
    // @deprecated as of Drupal 8.0. Use
    //   Drupal::languageManager()->getLanguage($type).
    $form['langcode'] = array(
      '#type' => 'language_select',
      '#title' => t('Language'),
      '#languages' => Language::STATE_ALL,
      // Default to the content language opposed to und (language not specified).
      '#default_value' => $tour->isNew() ? language(Language::STATE_ALL)->id : $tour->langcode,
    );
    $form['module'] = array(
      '#type' => 'textfield',
      '#title' => t('Module name'),
      '#description' => t('Each tour needs a module.'),
      '#required' => TRUE,
      '#default_value' => $tour->get('module'),
    );
    $form['paths'] = array(
      '#type' => 'textarea',
      '#title' => t('Paths'),
      '#default_value' => implode("\n", $tour->getPaths()),
      '#rows' => 5,
      '#description' => t('Provide a list of paths that this tour will be displayed on.'),
    );

    // Don't show the tips on the inital add.
    if ($tour->isNew()) {
      return $form;
    }

    // Start building the list of tips assigned to this tour.
    $form['tips'] = array(
      '#type' => 'table',
      '#header' => array(
        t('Label'),
        t('Weight'),
        t('Operations'),
      ),
      '#caption' => t('Tips provided by this tour.'),
      '#tabledrag' => array(
        array('order', 'sibling', 'tip-order-weight'),
      ),
      '#weight' => 40,
    );

    // Populate the table with the assigned tips.
    $tips = $tour->getTips();
    if (!empty($tips)) {
      foreach ($tips as $key => $tip) {
        $tip_id = $tip->get('id');
        $form['#data'][$tip_id] = $tip->export();
        $form['tips'][$tip_id]['#attributes']['class'][] = 'draggable';
        $form['tips'][$tip_id]['label'] = array(
          '#markup' => check_plain($tip->get('label')),
        );

        $form['tips'][$tip_id]['weight'] = array(
          '#type' => 'weight',
          '#title' => t('Weight for @title', array('@title' => $tip->get('label'))),
          '#delta' => 100,
          '#title_display' => 'invisible',
          '#default_value' => $tip->get('weight'),
          '#attributes' => array(
            'class' => array('tip-order-weight'),
          ),
        );

        // Provide operations links for the tip.
        $links = array();
        $tip_form = $tip->optionsForm();
        if (!empty($tip_form)) {
          $links['edit'] = array(
            'title' => t('edit'),
            'href' => 'admin/config/user-interface/tour/manage/' . $tour->id() . '/tip/edit/' . $tip_id,
          );
        }
        $links['delete'] = array(
          'title' => t('delete'),
          'href' => 'admin/config/user-interface/tour/manage/' . $tour->id() . '/tip/delete/' . $tip_id,
        );
        $form['tips'][$tip_id]['operations'] = array(
          '#type' => 'operations',
          '#links' => $links,
        );
      }
    }

    // Build the new tour tip addition form and add it to the tips list.
    $tip_definitions = \Drupal::service('plugin.manager.tour.tip')->getDefinitions();
    foreach ($tip_definitions as $tip => $definition) {
      $tip_definition_options[$tip] = $definition['title'];
    }

    // Unset the core "text" tip.
    unset($tip_definition_options['text']);

    $form['tips']['new'] = array(
      '#tree' => FALSE,
      '#weight' => isset($form_state['input']['weight']) ? $form_state['input']['weight'] : NULL,
      '#attributes' => array(
        'class' => array('draggable')
      ),
    );
    $form['tips']['new']['new'] = array(
      '#type' => 'select',
      '#title' => t('Tip'),
      '#title_display' => 'invisible',
      '#options' => $tip_definition_options,
      '#empty_option' => t('Select a new tip'),
    );
    $form['tips']['new']['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight for new tip'),
      '#title_display' => 'invisible',
      '#default_value' => count($form['tips']) - 1,
      '#attributes' => array(
        'class' => array('tip-order-weight'),
      ),
    );
    $form['tips']['new']['add'] = array(
      '#type' => 'submit',
      '#value' => t('Add'),
      '#validate' => array(array($this, 'tipValidate')),
      '#submit' => array(array($this, 'tipAdd')),
    );

    return $form;
  }

  /**
   * Validate handler.
   */
  public function tipValidate($form, &$form_state) {
    if (!$form_state['values']['new']) {
      form_error($form['tips']['new']['new'], t('Select a new tip.'));
    }
  }

  /**
   * Submit handler.
   */
  public function tipAdd($form, &$form_state) {
    $tour = $this->getEntity($form_state);

    // Merge the form values in with the current configuration.
    $tips = array();
    if (!empty($form_state['values']['tips'])) {
      foreach ($form_state['values']['tips'] as $key => $values) {
        $data = $form['#data'][$key];
        $tips[$key] = array_merge($data, $values);
      }
    }
    else {
      $tips = array();
    }
    $tour->set('tips', $tips);
    $tour->save();

    $manager = \Drupal::service('plugin.manager.tour.tip');
    $stub = $manager->createInstance($form_state['values']['new'], array());

    // If a form is available for this tip then redirect to a add page.
    $stub_form = $stub->optionsForm();
    if (isset($stub_form)) {
      // Redirect to the appropriate page to add this new tip.
      $path = 'admin/config/user-interface/tour/manage/' . $tour->id() . '/tip/add/' . $form_state['values']['new'];
      $form_state['redirect'] = array($path, array('query' => array('weight' => $form_state['values']['weight'])));
    }

  }

  /**
   * Overrides \Drupal\Core\Entity\EntityFormController::submit().
   */
  public function submit(array $form, array &$form_state) {
    // Filter out invalid characters and convert to an array.
    preg_replace('/(\r\n?|\n)/', '\n', $form_state['values']['paths']);
    $form_state['values']['paths'] = explode("\n", $form_state['values']['paths']);
    $form_state['values']['paths'] = array_map('trim', $form_state['values']['paths']);
    $form_state['values']['paths'] = array_filter($form_state['values']['paths']);

    // Merge the form values in with the current configuration.
    if (!empty($form_state['values']['tips'])) {
      foreach ($form_state['values']['tips'] as $key => $values) {
        $data = $form['#data'][$key];
        $form_state['values']['tips'][$key] = array_merge($data, $values);
      }
    }
    else {
      $form_state['values']['tips'] = array();
    }

    $entity = parent::submit($form, $form_state);
    $is_new = $entity->isNew();
    $entity->save();

    if ($is_new) {
      drupal_set_message(t('The %tour tour has been created.', array('%tour' => $entity->label())));
      $form_state['redirect'] = 'admin/config/user-interface/tour/manage/' . $entity->id();
    }
    else {
      drupal_set_message(t('Updated the %tour tour.', array('%tour' => $entity->label())));
      $form_state['redirect'] = 'admin/config/user-interface/tour';
    }

    return $entity;
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityFormController::delete().
   */
  public function delete(array $form, array &$form_state) {
    $entity = $this->getEntity($form_state);
    $form_state['redirect'] = 'admin/config/user-interface/tour/manage/' . $entity->id() . '/delete';
  }

}
