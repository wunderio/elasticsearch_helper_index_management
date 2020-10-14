<?php

namespace Drupal\elasticsearch_helper_index_management;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;

/**
 * Defines indexing status manager.
 */
class IndexingStatusManager implements IndexingStatusManagerInterface {

  /**
   * The database table for storing indexing status items.
   *
   * @var string
   */
  const DATABASE_TABLE = 'elasticsearch_helper_indexing_status';

  /**
   * Database instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Time instance.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * IndexingStatusManager constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   * @param \Drupal\Component\Datetime\TimeInterface $time
   */
  public function __construct(Connection $database, TimeInterface $time) {
    $this->database = $database;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public function updateStatus(array $item) {
    // Set created timestamp.
    $item['created'] = $this->time->getRequestTime();

    $this->database
      ->merge(static::DATABASE_TABLE)
      ->keys([
        'index_plugin' => $item['index_plugin'],
        'id' => $item['id'],
        'entity_type' => $item['entity_type'],
      ])
      ->fields($item)
      ->execute();
  }

  /**
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function getAll(array $parameter = []) {
    $query = $this
      ->database
      ->select(self::DATABASE_TABLE, 'tb');

    $query->fields('tb');

    foreach ($parameter as $field => $value) {
      $query->condition($field, $value);
    }

    return $query->execute()->fetchAll();
  }

  /**
   * {@inheritdoc}
   */
  public function count(array $parameter = []) {
    $query = $this
      ->database
      ->select(self::DATABASE_TABLE, 'tb');

    foreach ($parameter as $field => $value) {
      $query->condition($field, $value);
    }

    return $query->countQuery()->execute()->fetchField();
  }

}
