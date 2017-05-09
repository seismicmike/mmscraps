<?php

/**
 * @file
 * Contains \Drupal\tour_ui\TourTipFormController.
 */

namespace Drupal\tour_ui;

use Drupal\Core\Entity\EntityFormController;

/**
 * Form controller for the tour tip plugin edit forms.
 */
class TourTipFormController extends EntityFormController {

  /**
   * Overrides \Drupal\Core\Entity\EntityFormController::form().
   */
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);
    $form += $form_state['#tip']->optionsForm();
    return $form;
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityFormController::validate().
   */
  public function validate(array $form, array &$form_state) {
    // Determine if one of our tips already exist.
    $values = $form_state['values'];
    $tips = $form_state['#tour']->getTips();
    $tip_ids = array_map(function($data) {return $data->get('id');}, $tips);
    // Also if there are no initial tips then we don't need to check.
    if (empty($tips)) {
      return;
    }
    if (in_array($values['id'], $tip_ids) && !empty($form_state['#new'])) {
      form_error($form['label'], t('A tip with the same identifier exists.'));
    }
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityFormController::submit().
   */
  public function submit(array $form, array &$form_state) {
    $tour = $form_state['#tour'];
    $tip = $form_state['#tip'];
    $values = $form_state['values'];
    $exports = $tip->export();

    // Build a new tip.
    $new_tip = $tip->export();
    foreach ($exports as $export_id => $export) {
      $value = $values[$export_id];
      $new_tip[$export_id] = is_array($value) ? array_filter($value) : $value;
    }

    // Rebuild the tips.
    $new_tip_list = $tour->getTips();
    $new_tips = array();
    if (!empty($new_tip_list)) {
      foreach ($new_tip_list as $tip) {
        $new_tips[$tip->get('id')] = $tip->export();
      }
    }

    // Add our tip and save.
    $new_tips[$new_tip['id']] = $new_tip;
    $tour->set('tips', $new_tips);
    $tour->save();

    if (!empty($form_state['#new'])) {
      drupal_set_message(t('The %tip tip has been created.', array('%tip' => $new_tip['label'])));
    }
    else {
      drupal_set_message(t('Updated the %tip tip.', array('%tip' => $new_tip['label'])));
    }

    $form_state['redirect'] = 'admin/config/user-interface/tour/manage/' . $tour->id();
    return $tour;
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityFormController::delete().
   */
  public function delete(array $form, array &$form_state) {
    $entity = $this->getEntity($form_state);
    $form_state['redirect'] = 'admin/config/user-interface/tour/manage/' . $entity->get('id') . '/tip/delete/' . $form_state['#tip']->get('id');
  }

}
