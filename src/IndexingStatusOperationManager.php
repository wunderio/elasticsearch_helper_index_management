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
  public function setSuccessIndexingStatus(ElasticsearchIndexInterface $index_plugin, $object) {
    if ($object instanceof EntityInterface) {
      $this->setEntityIndexingStatus($index_plugin, $object, IndexingStatusManagerInterface::STATUS_SUCCESS);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setErrorIndexingStatus(ElasticsearchIndexInterface $index_plugin, $object) {
    if ($object instanceof EntityInterface) {
      $this->setEntityIndexingStatus($index_plugin, $object, IndexingStatusManagerInterface::STATUS_ERROR);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteIndexingStatus(ElasticsearchIndexInterface $index_plugin, $object) {
    if ($object instanceof EntityInterface) {
      $this->deleteEntityIndexingStatus($index_plugin, $object);
    }
  }

  /**
   * Sets indexing status for index-able entity.
   *
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $index_plugin
   *   The Elasticsearch index plugin which is indexing the object.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The index-able entity.
   * @param $status
   *   Status name.
   */
  protected function setEntityIndexingStatus(ElasticsearchIndexInterface $index_plugin, EntityInterface $entity, $status) {
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
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $index_plugin
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  protected function deleteEntityIndexingStatus(ElasticsearchIndexInterface $index_plugin, EntityInterface $entity) {
    $this->indexingStatusManager->clear([
      'index_plugin' => $index_plugin->getPluginId(),
      'id' => $entity->id(),
      'entity_type' => $entity->getEntityTypeId(),
    ]);
  }

}
