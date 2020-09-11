<?php

namespace Drupal\elasticsearch_helper_index_management\Plugin\QueueWorker;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Indexes entity during cron run.
 *
 * @QueueWorker(
 *   id = "elasticsearch_helper_index_management_reindex",
 *   title = @Translation("Re-index"),
 *   cron = {"time" = 60}
 * )
 */
class Reindex extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager
   */
  public $elasticsearchIndexManager;

  /**
   * Reindex constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager $elasticsearch_index_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ElasticsearchIndexManager $elasticsearch_index_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->elasticsearchIndexManager = $elasticsearch_index_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.elasticsearch_index.processor')
    );
  }

  /**
   * {@inheritdoc}
   *
   * Executes the reindexing.
   */
  public function processItem($data) {
    try {
      $plugin = $this->elasticsearchIndexManager->createInstance($data['plugin_id']);

      // Re-index the content.
      $plugin->reindex(['caller' => 'elasticsearch_helper_index_management']);
    }
    // Do not
    catch (PluginException $e) {
      throw new SuspendQueueException($e->getMessage());
    }
  }

}
