<?php

/**
 * @file
 * Provide views data for index management tables.
 */

/**
 * Implements hook_views_data().
 */
function elasticsearch_helper_index_management_views_data() {
  $class = '\Drupal\elasticsearch_helper_index_management\ViewsData';
  /** @var \Drupal\elasticsearch_helper_index_management\ViewsData $instance */
  $instance = \Drupal::service('class_resolver')->getInstanceFromDefinition($class);

  return $instance->getViewsData();
}
