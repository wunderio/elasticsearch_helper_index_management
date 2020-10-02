<?php

namespace Drupal\elasticsearch_helper_index_management\EventSubscriber;

use Drupal\elasticsearch_helper\ElasticsearchRequestWrapper;
use Drupal\elasticsearch_helper\Event\ElasticsearchOperationErrorEvent;
use Drupal\elasticsearch_helper\Event\ElasticsearchOperations;
use Drupal\elasticsearch_helper\Plugin\QueueWorker\IndexingQueueWorker;
use Drupal\elasticsearch_helper_index_management\OperationsManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Subscribes to Elasticsearch Helper Operation Error Event.
 */
class OperationError implements EventSubscriberInterface {

  /**
   * Index Management Queue.
   *
   * @var \Drupal\elasticsearch_helper_index_management\OperationsManager
   */
  protected $operationsManager;

  /**
   * Constructs a new ElasticsearchHelperIndexManagementEventSubscriber object.
   */
  public function __construct(OperationsManager $operationsManager) {
    $this->operationsManager = $operationsManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['elasticsearch_helper.operation_error'] = ['elasticsearchHelperOperationError'];

    return $events;
  }

  /**
   * This method is called when the elasticsearch_helper.operation_error
   * is dispatched.
   *
   * @param \Drupal\elasticsearch_helper\Event\ElasticsearchOperationErrorEvent $event
   *   The dispatched operation error event.
   */
  public function elasticsearchHelperOperationError(ElasticsearchOperationErrorEvent $event) {
    // When an item could not be re-indexed, add it to the flagged items queue
    // so that it could be reprocessed and if needed re-indexed.
    if ($event->getOperation() == ElasticsearchOperations::DOCUMENT_INDEX && $requestWrapper = $event->getRequestWrapper()) {
      $isIndexedByWorker = &drupal_static(IndexingQueueWorker::QUEUE_INDEXING_VAR_NAME);

      // Check if the indexing operation was done by the queue worker and if the
      // request wrapper contains an object before we flag it.
      if ($isIndexedByWorker && $object = $requestWrapper->getObject()) {
        $this->operationsManager->flagAsFailing($event->getPluginInstance(), $object);
      }
    }
  }

}
