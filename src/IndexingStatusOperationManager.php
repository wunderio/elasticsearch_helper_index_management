<?php

namespace Drupal\elasticsearch_helper_index_management;

use Drupal\Core\Entity\EntityInterface;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface;

/**
 * Service for providing methods related to indexing status operations.
 */
class IndexingStatusOperationManager implements IndexingStatusOperationManagerInterface {

  /**
   * Indexing status manager instance.
   *
   * @var \Drupal\elasticsearch_helper_index_management\IndexingStatusManagerInterface
   */
  protected $indexingStatusManager;

  /**
   * OperationsManager constructor.
   *
   * @param \Drupal\elasticsearch_helper_index_management\IndexingStatusManagerInterface $indexing_status_manager
   */
  public function __construct(IndexingStatusManagerInterface $indexing_status_manager) {
    $this->indexingStatusManager = $indexing_status_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function setSuccessIndexingStatus($object, ElasticsearchIndexInterface $index_plugin) {
    if ($object instanceof EntityInterface) {
      $this->setEntityIndexingStatus($object, IndexingStatusManagerInterface::STATUS_SUCCESS, $index_plugin);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setErrorIndexingStatus($object, ElasticsearchIndexInterface $index_plugin) {
    if ($object instanceof EntityInterface) {
      $this->setEntityIndexingStatus($object, IndexingStatusManagerInterface::STATUS_ERROR, $index_plugin);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteIndexingStatus($object, ElasticsearchIndexInterface $index_plugin = NULL) {
    if ($object instanceof EntityInterface) {
      $this->deleteEntityIndexingStatus($object, $index_plugin);
    }
  }

  /**
   * Sets indexing status for index-able entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The index-able entity.
   * @param $status
   *   Status name.
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $index_plugin
   *   The Elasticsearch index plugin which is indexing the object.
   */
  protected function setEntityIndexingStatus(EntityInterface $entity, $status, ElasticsearchIndexInterface $index_plugin) {
    $this->indexingStatusManager->updateStatus([
      'index_plugin' => $index_plugin->getPluginId(),
      'status' => $status,
      'id' => $entity->id(),
      'entity_type' => $entity->getEntityTypeId(),
    ]);
  }

  /**
   * Deletes indexing status for given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $index_plugin
   */
  protected function deleteEntityIndexingStatus(EntityInterface $entity, ElasticsearchIndexInterface $index_plugin = NULL) {
    $conditions = [
      'id' => $entity->id(),
      'entity_type' => $entity->getEntityTypeId(),
    ];

    if ($index_plugin) {
      $conditions['index_plugin'] = $index_plugin->getPluginId();
    }

    $this->indexingStatusManager->clear($conditions);
  }

}
