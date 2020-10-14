<?php

namespace Drupal\elasticsearch_helper_index_management\EventSubscriber;

use Drupal\elasticsearch_helper\Event\ElasticsearchEvents;
use Drupal\elasticsearch_helper\Event\ElasticsearchOperationRequestResultEvent;
use Drupal\elasticsearch_helper\Event\ElasticsearchOperations;
use Drupal\elasticsearch_helper_index_management\IndexingStatusManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Clears indexing status table on Elasticsearch index drop operation.
 */
class IndexingStatusFlush implements EventSubscriberInterface {

  /**
   * Indexing status manager instance.
   *
   * @var \Drupal\elasticsearch_helper_index_management\IndexingStatusManagerInterface
   */
  protected $indexingStatusManager;

  /**
   * IndexingStatusFlush constructor.
   *
   * @param \Drupal\elasticsearch_helper_index_management\IndexingStatusManagerInterface $indexing_status_manager
   */
  public function __construct(IndexingStatusManagerInterface $indexing_status_manager) {
    $this->indexingStatusManager = $indexing_status_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ElasticsearchEvents::OPERATION_REQUEST_RESULT] = ['onRequestResult'];

    return $events;
  }

  /**
   * Listens to Elasticsearch drop operation request result event.
   *
   * @param \Drupal\elasticsearch_helper\Event\ElasticsearchOperationRequestResultEvent $event
   */
  public function onRequestResult(ElasticsearchOperationRequestResultEvent $event) {
    $result = $event->getResult()->getResultBody();
    $request_wrapper = $event->getRequestWrapper();
    $plugin_instance = $request_wrapper->getPluginInstance();

    if ($request_wrapper->getOperation() == ElasticsearchOperations::INDEX_DROP && !empty($result['acknowledged'])) {
      // Clear the indexing statuses for affected index plugin.
      $this->indexingStatusManager->clear(['index_plugin' => $plugin_instance->getPluginId()]);
    }
  }

}
