<?php

namespace Drupal\elasticsearch_helper_index_management;

use Drupal\Core\Queue\QueueDatabaseFactory;

/**
 * Factory class for Elasticsearch Helper Index Management Queue.
 */
class QueueFactory extends QueueDatabaseFactory {

  /**
   * {@inheritdoc}
   */
  public function get($name) {
    return new Queue($name, $this->connection);
  }

}
