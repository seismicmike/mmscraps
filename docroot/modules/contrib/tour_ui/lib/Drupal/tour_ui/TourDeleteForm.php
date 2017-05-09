<?php

/**
 * @file
 * Contains \Drupal\tour_ui\TourDeleteForm.
 */

namespace Drupal\tour_ui;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Builds the form to delete a tour.
 */
class TourDeleteForm implements FormInterface {

  /**
   * Stores the tour entity being deleted.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'tour_ui_confirm_delete';
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, array &$form_state, EntityInterface $tour = NULL) {
    $this->entity = $tour;

    return confirm_form($form,
      t('Are you sure you want to delete the %tour tour?', array('%tour' => $this->entity->label())),
      'admin/config/user-interface/tour',
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
    $this->entity->delete();
    $form_state['redirect'] = 'admin/config/user-interface/tour';
    drupal_set_message(t('Deleted the %tour tour.', array('%tour' => $this->entity->label())));
  }

}
