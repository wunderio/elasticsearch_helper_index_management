<?php

namespace Drupal\elasticsearch_helper_index_management\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Subscribes to Elasticsearch Helper Operation Error Event.
 */
class OperationError implements EventSubscriberInterface {

  /**
   * Constructs a new ElasticsearchHelperIndexManagementEventSubscriber object.
   */
  public function __construct() {

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
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function elasticsearchHelperOperationError(Event $event) {
    // @todo, implement this.
  }

}
