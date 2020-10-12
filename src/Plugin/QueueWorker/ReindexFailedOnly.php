<?php

namespace Drupal\elasticsearch_helper_index_management\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager;
use Drupal\elasticsearch_helper_index_management\IndexingStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Re-indexes content that has previously failed to index.
 *
 * @QueueWorker(
 *   id = "elasticsearch_helper_index_management_reindex_failed_only",
 *   title = @Translation("Re-index failed items"),
 *   cron = {"time" = 60}
 * )
 */
class ReindexFailedOnly extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager
   */
  public $elasticsearchIndexManager;

  /**
   * @var \Drupal\elasticsearch_helper_index_management\IndexingStatusManagerInterface
   */
  protected $indexingStatusManager;

  /**
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * ReindexFailedOnly constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager $elasticsearch_index_manager
   * @param \Drupal\elasticsearch_helper_index_management\IndexingStatusManagerInterface $indexing_status_manager
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ElasticsearchIndexManager $elasticsearch_index_manager, IndexingStatusManagerInterface $indexing_status_manager, QueueFactory $queue_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->elasticsearchIndexManager = $elasticsearch_index_manager;
    $this->indexingStatusManager = $indexing_status_manager;
    $this->queueFactory = $queue_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.elasticsearch_index.processor'),
      $container->get('elasticsearch_helper_index_management.indexing_status_manager'),
      $container->get('queue')
    );
  }

  /**
   * {@inheritdoc}
   *
   * Executes the reindexing.
   */
  public function processItem($data) {
    // Get indexing queue.
    $queue = $this->queueFactory->get('elasticsearch_helper_indexing');

    // Get all failed items for given index plugin.
    $items = $this->indexingStatusManager->getAll([
      'index_plugin' => $data['plugin_id'],
      'status' => IndexingStatusManagerInterface::STATUS_ERROR,
    ]);

    foreach ($items as $item) {
      $queue->createItem([
        'entity_type' => $item->entity_type,
        'entity_id' => $item->id,
      ]);
    }
  }

}
