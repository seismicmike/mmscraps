<?php

/**
 * @file
 * Contains Drupal\tour_ui\TourUiBundle.
 */

namespace Drupal\tour_ui;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Tour UI dependency injection container.
 */
class TourUiBundle extends Bundle {

  /**
   * Overrides Symfony\Component\HttpKernel\Bundle\Bundle::build().
   */
  public function build(ContainerBuilder $container) {
    $container->register('tour_ui.controller', 'Drupal\tour_ui\Routing\TourUIController')
      ->addArgument(new Reference('plugin.manager.entity'));
  }

}
