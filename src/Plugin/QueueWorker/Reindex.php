<?php

namespace Drupal\elasticsearch_helper_index_management\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Indexes entity during cron run.
 *
 * @QueueWorker(
 *   id = "elasticsearch_helper_index_management_reindex",
 *   title = @Translation("Deferred re-index"),
 *   cron = {"time" = 60}
 * )
 */
class Reindex extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   *
   * Executes the entity process callback.
   */
  public function processItem($data) {
    call_user_func_array($data['callback'], $data['params']);
  }

}
