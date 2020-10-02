<?php

namespace Drupal\elasticsearch_helper_index_management;

use Drupal\Core\Entity\EntityInterface;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface;

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
   * @param Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $indexPlugin
   *   The elasticsearch index plugin which is indexing the object.
   * @param object $object
   *   The object from request wrapper.
   */
  public function flagAsFailing(ElasticsearchIndexInterface $indexPlugin, $object) {
    // @todo, support non-entities.
    if ($object instanceof EntityInterface) {
      $this->indexItemManager->addItem([
        'index_plugin' => $indexPlugin->getPluginId(),
        'entity_type' => $object->getEntityTypeId(),
        'entity_id' => $object->id(),
        'flag' => IndexItemManager::FLAG_FAIL,
      ]);
    }
  }

}
