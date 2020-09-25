<?php

namespace Drupal\elasticsearch_helper_index_management;

use Drupal\Core\Entity\EntityInterface;

/**
 * Service for providing methods related to index management operations.
 */
class OperationsManager {
  /**
   * The Index Item Manager.
   *
   * @var \Drupal\elasticsearch_helper_index_management\IndexItemManagerInterface
   */
  protected $indexItemManager;

  /**
   * Constructs a new Operations Manager object.
   */
  public function __construct(IndexItemManagerInterface $indexItemManager) {
    $this->indexItemManager = $indexItemManager;
  }

  /**
   * Flag an object as failing to index.
   *
   * @param mixed $object
   *   The object from request wrapper.
   */
  public function flagAsFailing($object) {
    // @todo, support non-entities.
    if ($object instanceof EntityInterface) {
      $this->indexItemManager->addItem([
        'entity_type' => $object->getEntityTypeId(),
        'entity_id' => $object->id(),
        'flag' => IndexItemManager::FLAG_FAIL,
      ]);
    }
  }

}
