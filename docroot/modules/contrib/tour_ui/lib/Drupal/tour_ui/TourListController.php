<?php

/**
 * Contains \Drupal\tour_ui\TourListController.
 */

namespace Drupal\tour_ui;

use Drupal\Core\Config\Entity\ConfigEntityListController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of tours.
 */
class TourListController extends ConfigEntityListController {

  /**
   * Overrides \Drupal\Core\Entity\EntityListController::buildHeader().
   */
  public function buildHeader() {
    $row['id'] = t('Id');
    $row['label'] = t('Label');
    $row['paths'] = t('Paths');
    $row['tips'] = t('Number of tips');
    $row['operations'] = t('Operations');
    return $row;
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityListController::buildRow().
   */
  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);

    $data['id'] = check_plain($entity->id());
    $data['label'] = check_plain($entity->label());
    // Include the paths this tour is used on.
    $data['paths'] = implode('<br>', array_map(function($path) {
      // If the path contains no wildcards, output it as a link.
      if (strpos($path, '*') === FALSE) {
        $options =  array(
          'query' => array(
            'tour' => 1,
          )
        );
        return l($path, $path, $options);
      }
      return check_plain('/' . $path);
    }, $entity->get('paths')));
    // Count the number of tips.
    $data['tips'] = count($entity->getTips());
    $data['operations'] = $row['operations'];
    // Wrap the whole row so that the entity ID is used as a class.
    return array(
      'data' => $data,
      'class' => array(
        $entity->id(),
      ),
    );
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityListController::getOperations().
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    $uri = $entity->uri('edit-form');

    $operations['edit'] = array(
      'title' => t('Edit'),
      'href' => $uri['path'],
      'options' => $uri['options'],
      'weight' => 1,
    );
    $operations['delete'] = array(
      'title' => t('Delete'),
      'href' => $uri['path'] . '/delete',
      'options' => $uri['options'],
      'weight' => 2,
    );

    // Tours do not support being enabled or disabled.
    //unset($operations['enable'], $operations['disable']);
    return $operations;
  }

}
