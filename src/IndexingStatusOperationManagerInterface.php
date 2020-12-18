<?php

namespace Drupal\elasticsearch_helper_index_management;

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
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $index_plugin|null
   *   The Elasticsearch index plugin which is indexing the object.
   */
  public function deleteIndexingStatus($object, ElasticsearchIndexInterface $index_plugin = NULL);

}
