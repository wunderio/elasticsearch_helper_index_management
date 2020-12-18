<?php

namespace Drupal\elasticsearch_helper_index_management\EventSubscriber;

use Drupal\elasticsearch_helper\ElasticsearchRequestResultInterface;
use Drupal\elasticsearch_helper\Event\ElasticsearchEvents;
use Drupal\elasticsearch_helper\Event\ElasticsearchOperationErrorEvent;
use Drupal\elasticsearch_helper\Event\ElasticsearchOperationRequestResultEvent;
use Drupal\elasticsearch_helper\Event\ElasticsearchOperations;
use Drupal\elasticsearch_helper_index_management\IndexingStatusOperationManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Updates successful and failed indexing statuses.
 */
class IndexingStatus implements EventSubscriberInterface {

  /**
   * Indexing status operations manager instance.
   *
   * @var \Drupal\elasticsearch_helper_index_management\IndexingStatusOperationManagerInterface
   */
  protected $indexingStatusOperationManager;

  /**
   * IndexingStatus constructor.
   *
   * @param \Drupal\elasticsearch_helper_index_management\IndexingStatusOperationManagerInterface $operationsManager
   */
  public function __construct(IndexingStatusOperationManagerInterface $operationsManager) {
    $this->indexingStatusOperationManager = $operationsManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ElasticsearchEvents::OPERATION_ERROR] = ['onOperationError'];
    $events[ElasticsearchEvents::OPERATION_REQUEST_RESULT] = ['onRequestResult'];

    return $events;
  }

  /**
   * Listens to Elasticsearch operation error event.
   *
   * Sets the indexing status to "error". This allows selective reindexing
   * based on failed indexing status.
   *
   * @param \Drupal\elasticsearch_helper\Event\ElasticsearchOperationErrorEvent $event
   *   The dispatched operation error event.
   */
  public function onOperationError(ElasticsearchOperationErrorEvent $event) {
    if ($event->getOperation() == ElasticsearchOperations::DOCUMENT_INDEX && $object = $event->getObject()) {
      // Mark indexing status as failed.
      $this->indexingStatusOperationManager->setErrorIndexingStatus($object, $event->getPluginInstance());
    }
  }

  /**
   * Listens to Elasticsearch operation request result event.
   *
   * @param \Drupal\elasticsearch_helper\Event\ElasticsearchOperationRequestResultEvent $event
   */
  public function onRequestResult(ElasticsearchOperationRequestResultEvent $event) {
    $result = $event->getResult();
    $request_wrapper = $event->getRequestWrapper();
    $operation = $request_wrapper->getOperation();

    if ($operation == ElasticsearchOperations::DOCUMENT_INDEX) {
      if ($this->isIndexResultSuccessful($result)) {
        // Mark indexing status as successful.
        $this->indexingStatusOperationManager->setSuccessIndexingStatus($request_wrapper->getObject(), $request_wrapper->getPluginInstance());
      }
    }
  }

  /**
   * Returns TRUE if document index result is successful.
   *
   * @param \Drupal\elasticsearch_helper\ElasticsearchRequestResultInterface $result
   *
   * @return bool
   */
  protected function isIndexResultSuccessful(ElasticsearchRequestResultInterface $result) {
    $body = $result->getResultBody();

    return isset($body['result']) && in_array($body['result'], ['created', 'updated']);
  }

}
