<?php

namespace Drupal\elasticsearch_helper_index_management;

use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Entity\EntityInterface;

/**
 * Service for providing methods related index management operations.
 */
class OperationsManager {

  /**
   * Queue prefix name;
   *
   * @var string
   */
  const QUEUE_NAME_PREFIX = 'elasticsearch_helper_index_management_op_';

  /**
   * Queue type name for items flaged as failed.
   *
   * @var string
   */
  const FAILED_QUEUE_TYPE = 'failed';

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Drupal\elasticsearch_helper_index_management\QueueFactory definition.
   *
   * @var \Drupal\elasticsearch_helper_index_management\QueueFactory
   */
  protected $queueFactory;

  /**
   * Constructs a new TaskManager object.
   */
  public function __construct(Connection $database, QueueFactory $queueFactory) {
    $this->database = $database;
    $this->queueFactory = $queueFactory;
  }

  /**
   * Flag an object as failing to index.
   *
   * @param mixed $object
   *   The object from request wrapper.
   */
  public function flagAsFailing($object) {
    $this->addToOperationsQueue($object, self::FAILED_QUEUE_TYPE);
  }

  /**
   * Add an object to one of the operations queue.
   *
   * @param mixed $object
   *   The object from request wrapper.
   * @param string $queueTypeName
   *   The queue type name.
   */
  protected function addToOperationsQueue($object, $queueTypeName) {
    $queue = $this->queueFactory->get(self::QUEUE_NAME_PREFIX . $queueTypeName);

    // @todo, support non-entities.
    if ($object instanceof EntityInterface) {
      $queue->createItem([
        'entity_type' => $object->getEntityTypeId(),
        'entity_id' => $object->id(),
      ]);
    }
  }

}
