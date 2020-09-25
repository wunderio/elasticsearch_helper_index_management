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
   *
   * @param array $parameter
   *   An array of parameters.
   */
  public function clear(array $parameter = []);

  /**
   * Get all items.
   *
   * @param array $parameter
   *   An array of parameters.
   *
   * @return array
   *   An array of items.
   */
  public function getAll(array $parameter = []);

  /**
   * Get all items count.
   *
   * @param array $parameter
   *   An array of parameters.
   *
   * @return array
   *   An array of items.
   */
  public function countAll(array $parameter = []);

}
