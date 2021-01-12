<?php

namespace Drupal\elasticsearch_helper_index_management;

use Drupal\Core\Database\Driver\mysql\Connection;
use Throwable;

/**
 * Class IndexVersionManager.
 *
 * Service for providing methods related to index version operations.
 */
class IndexVersionManager implements IndexVersionManagerInterface {
  const TABLE = 'elasticsearch_helper_indexing_version';

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Constructs of IndexVersionManager class.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritDoc}
   */
  public function getCurrentVersion($plugin_id) {
    $query = $this->database->select(static::TABLE, 'tb');
    $query->fields('tb', ['version']);
    $query->condition('index_plugin', $plugin_id);
    $version_record = $query->execute()->fetch();

    if ($version_record) {
      return $version_record->version;
    }

    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function incrementVersion($plugin_id): bool {
    $current = $this->getCurrentVersion($plugin_id);

    $version = $current ? $current + 1 : 1;

    if ($current) {
      $query = $this->database->update(static::TABLE)
        ->fields(['version' => $version])
        ->condition('index_plugin', $plugin_id);
    }
    else {
      $query = $this->database->insert(static::TABLE)
        ->fields([
          'version' => $version,
          'index_plugin' => $plugin_id,
        ]);
    }

    try {
      $query->execute();
      return TRUE;
    }
    catch (Throwable $e) {
      return FALSE;
    }

    // @todo Setup index.
  }

  /**
   * {@inheritDoc}
   */
  public function decrementVersion($plugin_id): bool {
    $current = $this->getCurrentVersion($plugin_id);

    if ($current && $current > 1) {
      $version = $current - 1;

      return (bool) $this->database->update(static::TABLE)
        ->fields(['version' => $version])
        ->condition('index_plugin', $plugin_id)
        ->execute();
    }

    return FALSE;
  }

}
