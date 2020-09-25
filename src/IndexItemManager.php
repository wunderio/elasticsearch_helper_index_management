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
  public function clear(array $parameters = []) {
    if ($parameters) {
      $query = $this
        ->database
        ->delete(self::DATABASE_TABLE);

      foreach ($parameters as $field => $value) {
        $query->condition($field, $value);
      }

      $query->execute();
    }
    // Clear everything if no parameters are passed.
    else {
      $this
        ->database
        ->truncate(self::DATABASE_TABLE);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getAll(array $parameter = []) {
    $query = $this
      ->database
      ->select(self::DATABASE_TABLE, 'tb');

    $query->fields('tb', ['entity_type', 'entity_id', 'flag', 'created']);

    foreach ($parameter as $field => $value) {
      $query->condition($field, $value);
    }

    return $query->execute()->fetchAll();
  }

  /**
   * {@inheritDoc}
   */
  public function countAll(array $parameter = []) {
    $query = $this
      ->database
      ->select(self::DATABASE_TABLE, 'tb');

    foreach ($parameter as $field => $value) {
      $query->condition($field, $value);
    }

    return $query->countQuery()->execute()->fetchField();
  }

}
