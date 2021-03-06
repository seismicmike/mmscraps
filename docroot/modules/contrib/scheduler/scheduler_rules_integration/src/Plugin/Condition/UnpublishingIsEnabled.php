<?php

namespace Drupal\scheduler_rules_integration\Plugin\Condition;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesConditionBase;

/**
 * Provides 'Unpublishing is enabled' condition.
 *
 * @Condition(
 *   id = "scheduler_condition_unpublishing_is_enabled",
 *   label = @Translation("Node type is enabled for scheduled unpublishing"),
 *   category = @Translation("Scheduler"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node",
 *       label = @Translation("The node to test for scheduling properties")
 *     )
 *   }
 * )
 */
class UnpublishingIsEnabled extends RulesConditionBase {

  /**
   * Determines whether scheduled unpublishing is enabled for this node type.
   *
   * @return
   *   TRUE if scheduled unpublishing is enabled for the node type, FALSE if not.
   */
  public function evaluate() {
    $node = $this->getContextValue('node');
    $config = \Drupal::config('scheduler.settings');
    return ($node->type->entity->getThirdPartySetting('scheduler', 'unpublish_enable', $config->get('default_unpublish_enable')));
  }

}
