<?php

namespace Drupal\elasticsearch_helper_index_management;

/**
 * Defines IndexItemStatusManager Interface.
 *
 * A service for managing index item flags or states.
 */
interface IndexItemManagerInterface {

  /**
   * Add an item.
   *
   * @param array $item
   *   Item as an array.
   */
  public function addItem(array $item);

  /**
   * Clear all items.
   */
  public function clear();

  /**
   * Get all items by parameter.
   *
   * @param array $parameter
   *   An array of parameters.
   *
   * @return array
   *   An array of items.
   */
  public function getAll(array $parameter = []);

}
