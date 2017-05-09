<?php

/**
 * @file
 * Contains \Drupal\tour_ui\Routing\TourUIController.
 */

namespace Drupal\tour_ui\Routing;

use Drupal\Core\Entity\EntityManager;
use Drupal\tour\Entity\Tour;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles page returns for tour.
 */
class TourUIController {

  /**
   * The manager of this tour UI.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $manager;

  /**
   * Construction function for the tour UI controller.
   */
  public function __construct(EntityManager $manager) {
    $this->manager = $manager;
  }

  /**
   * Provides a listing form for a tour entity.
   *
   * @return array
   *   A renderable form array.
   */
  public function listing() {
    return $this->manager->getListController('tour')->render();
  }

  /**
   * Provides a creation form for tour.
   *
   * @return array
   *   A renderable form array.
   */
  public function add() {
    $tour = $this->manager->getStorageController('tour')->create(array());
    return entity_get_form($tour);
  }

  /**
   * Provides an edit form for a tour entity.
   *
   * @param $tour
   *   The tour that will be modified.
   *
   * @return array
   *   A renderable form array.
   */
  public function edit(Tour $tour) {
    return entity_get_form($tour);
  }

  /**
   * Provides a creation form for a new tip to be added to a tour entity.
   *
   * @param $tour
   *   The tour in which the tip needs to be added to.
   *
   * @param $type
   *   The type of tip that will be added to the tour.
   *
   * @return array
   *   A renderable form array.
   */
  public function addTip(Tour $tour, $type = '') {
    // We need a type to build this form.
    if (!$type) {
      throw new NotFoundHttpException();
    }

    // Default values.
    $request = \Drupal::service('request');
    $defaults = array(
      'plugin' => $type,
      'weight' => $request->query->get('weight'),
    );

    // Build a new stub tip.
    $manager = \Drupal::service('plugin.manager.tour.tip');
    $stub = $manager->createInstance($type, $defaults);

    // Attach the tour, tip and if it's new to the form.
    $form_state['#tour'] = $tour;
    $form_state['#tip'] = $stub;
    $form_state['#new'] = TRUE;

    return entity_get_form($tour, 'tips', $form_state);
  }

  /**
   * Provides an edit form for tip to be updated against a tour entity.
   *
   * @param $tour
   *   The tour in which the tip is being edited against.
   *
   * @param $tip
   *   The identifier of tip that will be edited against the tour.
   *
   * @return array
   *   A renderable form array.
   */
  public function editTip(Tour $tour, $tip = '') {
    // We need a tip to build this form.
    if (!$tip && !$tour) {
      throw new NotFoundHttpException();
    }

    // If the tip doesn't exist return.
    $tips = array_keys($tour->getTips());
    if (!in_array($tip, $tips)) {
      throw new NotFoundHttpException();
    }

    // Attach the tour, tip and if it's new to the form.
    $form_state['#tour'] = $tour;
    $form_state['#tip'] = $tour->getTip($tip);

    return entity_get_form($tour, 'tips', $form_state);
  }

}
