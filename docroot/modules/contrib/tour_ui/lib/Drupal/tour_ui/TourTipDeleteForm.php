<?php

/**
 * @file
 * Contains \Drupal\tour_ui\TourTipDeleteForm.
 */

namespace Drupal\tour_ui;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Builds the form to delete a tour tip.
 */
class TourTipDeleteForm implements FormInterface {

  /**
   * Stores the tour entity being deleted.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * Stores the tour tip candidate for deletion.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $tip;

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'tour_ui_tip_confirm_delete';
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, array &$form_state, EntityInterface $tour = NULL, $tip = '') {
    $this->entity = $tour;
    $this->tip = $tour->getTip($tip);

    return confirm_form($form,
      t('Are you sure you want to delete the %tour tour %tip tip?', array('%tour' => $this->entity->label(), '%tip' => $this->tip->get('label'))),
      'admin/config/user-interface/tour/manage/' . $this->entity->id(),
      t('This action cannot be undone.'),
      t('Delete'),
      t('Cancel')
    );
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::validateForm().
   */
  public function validateForm(array &$form, array &$form_state) {
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, array &$form_state) {
    // Rebuild the tips and remove the irrelevant one.
    $candidate = $this->tip->get('id');
    $tips = array();
    foreach ($this->entity->getTips() as $tip) {
      $tip_id = $tip->get('id');
      if ($tip_id == $candidate) {
        continue;
      }
      $tips[$tip_id] = $tip->export();
    }
    $this->entity->set('tips', $tips);
    $this->entity->save();

    $form_state['redirect'] = 'admin/config/user-interface/tour/manage/' . $this->entity->id();
    drupal_set_message(t('Deleted the %tour tour %tip tip.', array('%tour' => $this->entity->label(), '%tip' => $this->tip->get('label'))));
  }

}
