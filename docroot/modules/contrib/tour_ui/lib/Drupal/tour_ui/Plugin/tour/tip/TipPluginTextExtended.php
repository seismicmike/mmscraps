<?php

/**
 * @file
 * Contains \Drupal\tour_ui\Plugin\tour\tip\TipPluginTextExtended.
 */

namespace Drupal\tour_ui\Plugin\tour\tip;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Drupal\tour\Annotation\Tip;
use Drupal\tour\TipPluginBase;
use Drupal\tour\Plugin\tour\tip\TipPluginText;
use Symfony\Component\DependencyInjection\ContainerInterface;
  
/**
 * Displays some text as a tip.
 *
 * @Tip(
 *   id = "text_extended",
 *   title = @Translation("Text")
 * )
 */
class TipPluginTextExtended extends TipPluginText {

  /**
   * Overrides \Drupal\tour\Plugin\tour\tour\TourPluginBase::export();
   */
  public function export() {
    $names = array(
      'id',
      'plugin',
      'label',
      'weight',
      'attributes',
      'body',
      'location',
    );
    foreach ($names as $name) {
      $properties[$name] = $this->get($name);
    }
    return $properties;
  }

  /**
   * Overrides \Drupal\tour\Plugin\tour\tour\TourPluginInterface::optionsForm().
   */
  public function optionsForm() {
    $form = array();
    $id = $this->get('id');
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#required' => TRUE,
      '#default_value' => $this->get('label'),
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#machine_name' => array(
        'exists' => '_tour_load',
        'replace_pattern' => '[^a-z0-9-]+',
        'replace' => '-',
      ),
      '#default_value' => $id,
      '#disabled' => !empty($id),
    );
    $form['plugin'] = array(
      '#type' => 'value',
      '#value' => $this->get('plugin'),
    );
    $form['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight'),
      '#default_value' => $this->get('weight'),
      '#attributes' => array(
        'class' => array('tip-order-weight'),
      ),
    );
    
    $attributes = $this->getAttributes();
    $form['attributes'] = array(
      '#type' => 'fieldset',
      '#title' => t('Attributes'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#tree' => TRUE,
    );

    // Determine the type identifier of the tip.
    if (!empty($attributes['data-id'])) {
      $tip_type = 'data-id';
    }
    else if (!empty($attributes['data-class'])) {
      $tip_type = 'data-class';
    }
    else {
      $tip_type = 'modal';
    }
    $form['attributes']['selector_type'] = array(
      '#type' => 'select',
      '#title' => t('Selector type'),
      '#description' => t('The type of selector that this tip will target.'),
      '#options' => array(
        'data-id' => t('Data ID'),
        'data-class' => t('Data Class'),
        'modal' => t('Modal'),
      ),
      '#default_value' => $tip_type,
      '#element_validate' => array(array($this, 'optionsFormValidate')),
    );
    $form['attributes']['data-id'] = array(
      '#type' => 'textfield',
      '#title' => t('Data id'),
      '#description' => t('Provide the ID of the page element.'),
      '#field_prefix' => '#',
      '#default_value' => !empty($attributes['data-id']) ? $attributes['data-id'] : '',
      '#states' => array(
        'visible' => array(
          'select[name="attributes[selector_type]"]' => array('value' => 'data-id'),
        ),
        'enabled' => array(
          'select[name="attributes[selector_type]"]' => array('value' => 'data-id'),
        ),
      ),
    );
    $form['attributes']['data-class'] = array(
      '#type' => 'textfield',
      '#title' => t('Data class'),
      '#description' => t('Provide the Class of the page element.'),
      '#field_prefix' => '.',
      '#default_value' => !empty($attributes['data-class']) ? $attributes['data-class'] : '',
      '#states' => array(
        'visible' => array(
          'select[name="attributes[selector_type]"]' => array('value' => 'data-class'),
        ),
        'enabled' => array(
          'select[name="attributes[selector_type]"]' => array('value' => 'data-class'),
        ),
      ),
    );

    $form['location'] = array(
      '#type' => 'select',
      '#title' => t('Location'),
        '#options' => array(
        'top' => t('Top'),
        'bottom' => t('Bottom'),
        'left' => t('Left'),
        'right' => t('Right'),
      ),
      '#default_value' => $this->get('location'),
    );
    $form['body'] = array(
      '#type' => 'textarea',
      '#title' => t('Body'),
      '#required' => TRUE,
      '#default_value' => $this->get('body'),
    );
    return $form;
  }

  /**
   * Validates the tip optionsForm().
   *
   * @param $element
   *   The form element that has the validate attached.
   *
   * @param $form_state
   *   The state of the form after submission.
   *
   * @param $form
   *   The form array. 
   */
  function optionsFormValidate($element, &$form_state, $form) {
    $values = $form_state['values'];
    $selector_type = $values['attributes']['selector_type'];
    unset($form_state['values']['attributes']['selector_type']);

    // If modal we need to ensure that there is no data-id or data-class specified.
    if ($selector_type == 'modal') {
      unset($form_state['values']['attributes']['data-id']);
      unset($form_state['values']['attributes']['data-class']);
    }

    // If data-id was selected and no id provided.
    if ($selector_type == 'data-id' && empty($values['attributes']['data-id'])) {
      form_error($form['attributes']['data-id'], t('Please provide a data id.'));
    }

    // If data-class was selected and no class provided.
    if ($selector_type == 'data-class' && empty($values['attributes']['data-class'])) {
      form_error($form['attributes']['data-class'], t('Please provide a data class.'));
    }

    // Remove the data-class value if data-id is provided.
    if ($selector_type == 'data-id') {
      unset($form_state['values']['attributes']['data-class']);
    }

    // Remove the data-id value is data-class is provided.
    if ($selector_type == 'data-class') {
      unset($form_state['values']['attributes']['data-id']);
    }
  }
}
