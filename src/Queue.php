<?php

namespace Drupal\elasticsearch_helper_index_management;

use Drupal\Core\Queue\DatabaseQueue;

/**
 * Elasticsearch Helper Index management internal queue.
 */
class Queue extends DatabaseQueue {

  /**
   * The database table name for the queue.
   */
  const TABLE_NAME = 'queue_elasticsearch_helper_index_manager';

  /**
   * {@inheritdoc}
   */
  protected function doCreateItem($data) {
    // Serialize the data.
    $serialized = serialize($data);

    $query = $this->connection
      ->merge(static::TABLE_NAME)
      ->keys([
        'name' => $this->name,
        'entity_type' => $data['entity_type'],
        'entity_id' => $data['entity_id'],
      ])
      ->fields([
        'name' => $this->name,
        'data' => $serialized,
        'created' => time(),
        'entity_type' => $data['entity_type'],
        'entity_id' => $data['entity_id'],
      ]);

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function schemaDefinition() {
    $schema = parent::schemaDefinition();

    $schema['fields']['entity_type'] = [
      'type' => 'varchar_ascii',
      'length' => 255,
      'not null' => TRUE,
      'default' => '',
      'description' => 'The entity type id.',
    ];

    $schema['fields']['entity_id'] = [
      'type' => 'varchar_ascii',
      'length' => 255,
      'not null' => TRUE,
      'default' => '',
      'description' => 'The entity id.',
    ];

    $schema['indexes']['entity'] = ['entity_type', 'entity_id'];
    $schema['indexes']['flag'] = ['flag'];

    return $schema;
  }

}
