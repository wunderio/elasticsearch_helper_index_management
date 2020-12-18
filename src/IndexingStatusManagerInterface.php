<?php

namespace Drupal\elasticsearch_helper_index_management;

/**
 * Defines indexing status manager interface.
 */
interface IndexingStatusManagerInterface {

  /**
   * Status constant value for successfully indexed item.
   */
  const STATUS_SUCCESS = 'success';

  /**
   * Status constant value for failed item.
   */
  const STATUS_ERROR = 'error';

  /**
   * Updates indexing status.
   *
   * @param array $item
   *   Status values as an array.
   */
  public function updateStatus(array $item);

  /**
   * Clear all status items.
   *
   * @param array $parameters
   *   An array of parameters.
   */
  public function clear(array $parameters = []);

  /**
   * Get all status items.
   *
   * @param array $parameter
   *   An array of parameters.
   *
   * @return array
   *   An array of status items.
   */
  public function getAll(array $parameter = []);

  /**
   * Count all status items.
   *
   * @param array $parameter
   *   An array of parameters.
   *
   * @return int
   */
  public function count(array $parameter = []);

}
