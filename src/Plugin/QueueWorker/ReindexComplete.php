<?php

namespace Drupal\elasticsearch_helper_index_management\Plugin\QueueWorker;

/**
 * Performs complete content re-index (with index drop and setup).
 *
 * @QueueWorker(
 *   id = "elasticsearch_helper_index_management_reindex_complete",
 *   title = @Translation("Complete re-index"),
 *   cron = {"time" = 60}
 * )
 */
class ReindexComplete extends Reindex {

  /**
   * {@inheritdoc}
   *
   * Executes the reindexing.
   */
  public function processItem($data) {
    $plugin = $this->elasticsearchIndexManager->createInstance($data['plugin_id']);

    // Drop the indices.
    $plugin->drop();

    // Create the indices.
    $plugin->setup();

    // Re-index the content.
    $plugin->reindex(['caller' => 'elasticsearch_helper_index_management']);
  }

}
