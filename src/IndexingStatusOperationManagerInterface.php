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
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $index_plugin
   *   The Elasticsearch index plugin which is indexing the object.
   * @param mixed $object
   *   The index-able object.
   */
  public function setSuccessIndexingStatus(ElasticsearchIndexInterface $index_plugin, $object);

  /**
   * Sets failing indexing status for index-able object.
   *
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $index_plugin
   *   The Elasticsearch index plugin which is indexing the object.
   * @param mixed $object
   *   The index-able object.
   */
  public function setErrorIndexingStatus(ElasticsearchIndexInterface $index_plugin, $object);

  /**
   * Deletes indexing status item from the table.
   *
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $index_plugin
   * @param $object
   *
   * @return mixed
   */
  public function deleteIndexingStatus(ElasticsearchIndexInterface $index_plugin, $object);

}
