<?php

namespace Drupal\elasticsearch_helper_index_management;

use Drupal\Core\Database\Driver\mysql\Connection;

/**
 * Defines the class of the Index Item Manager Service.
 */
class IndexItemManager implements IndexItemManagerInterface {

  /**
   * The database table for storing index items.
   *
   * @var string
   */
  const DATABASE_TABLE = 'elasticsearch_helper_index_items';

  /**
   * Flag constant value for failed items.
   */
  const FLAG_FAIL = 'failed';

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Constructs a new IndexItemStatusManager object.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritDoc}
   */
  public function addItem($item) {
    // Set created timestamp.
    $item['created'] = \Drupal::time()->getRequestTime();

    $this->database
      ->merge(static::DATABASE_TABLE)
      ->keys([
        'entity_type' => $item['entity_type'],
        'entity_id' => $item['entity_id'],
      ])
      ->fields($item)
      ->execute();
  }

  /**
   * {@inheritDoc}
   */
  public function clear() {
    $this
      ->database
      ->truncate(self::DATABASE_TABLE);
  }

  /**
   * {@inheritDoc}
   */
  public function getAll(array $parameter = []) {
    $query = $this
      ->database
      ->select(self::DATABASE_TABLE);

    foreach ($parameter as $field => $value) {
      $query->condition($field, $value);
    }

    return $query->execute()->fetchAll();
  }

}
