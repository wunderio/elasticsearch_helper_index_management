<?php

namespace Drupal\elasticsearch_helper_index_management;

use Drupal\Core\Entity\EntityInterface;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface;

/**
 * Defines indexing status operation manager interface.
 */
interface IndexingStatusOperationManagerInterface {

  /**
   * Sets successful indexing status for index-able object.
   *
   * @param mixed $object
   *   The index-able object.
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $index_plugin
   *   The Elasticsearch index plugin which is indexing the object.
   */
  public function setSuccessIndexingStatus($object, ElasticsearchIndexInterface $index_plugin);

  /**
   * Sets failing indexing status for index-able object.
   *
   * @param mixed $object
   *   The index-able object.
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $index_plugin
   *   The Elasticsearch index plugin which is indexing the object.
   */
  public function setErrorIndexingStatus($object, ElasticsearchIndexInterface $index_plugin);

  /**
   * Deletes indexing status item from the table.
   *
   * @param $object
   *   The index-able object.
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface|null $index_plugin
   *   The Elasticsearch index plugin which is indexing the object.
   */
  public function deleteIndexingStatus($object, ElasticsearchIndexInterface $index_plugin = NULL);

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
  public function setEntityIndexingStatus(EntityInterface $entity, $status, ElasticsearchIndexInterface $index_plugin);

}
