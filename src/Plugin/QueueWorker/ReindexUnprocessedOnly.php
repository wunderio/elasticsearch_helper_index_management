<?php

namespace Drupal\elasticsearch_helper_index_management\Plugin\QueueWorker;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager;
use Drupal\elasticsearch_helper_index_management\IndexingStatusManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Re-indexes content that has previously failed to index.
 *
 * @QueueWorker(
 *   id = "elasticsearch_helper_index_management_reindex_unprocessed_only",
 *   title = @Translation("Re-index unprocessed items"),
 *   cron = {"time" = 60}
 * )
 */
class ReindexUnprocessedOnly extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager
   */
  public $elasticsearchIndexManager;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * ReindexUnprocessedOnly constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager $elasticsearch_index_manager
   * @param \Drupal\Core\Database\Connection $database
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ElasticsearchIndexManager $elasticsearch_index_manager, Connection $database, EntityTypeManagerInterface $entity_type_manager, QueueFactory $queue_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->elasticsearchIndexManager = $elasticsearch_index_manager;
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('queue')
    );
  }

  /**
   * {@inheritdoc}
   *
   * Executes the reindexing.
   */
  public function processItem($data) {
    // Get plugin definition.
    $plugin_definition = $this->elasticsearchIndexManager->getDefinition($data['plugin_id']);

    if (isset($plugin_definition['entityType'])) {
      $entity_type_id = isset($plugin_definition['entityType']) ? $plugin_definition['entityType'] : NULL;
      $bundle = isset($plugin_definition['bundle']) ? $plugin_definition['bundle'] : NULL;

      if ($items = $this->getUnprocessedItems($entity_type_id, $bundle)) {
        // Get indexing queue.
        $queue = $this->queueFactory->get('elasticsearch_helper_indexing');

        foreach ($items as $item) {
          $queue->createItem([
            'entity_type' => $item->entity_type,
            'entity_id' => $item->entity_id,
          ]);
        }
      }
    }
  }

  /**
   * Returns a list of unprocessed entity IDs.
   *
   * This method compares given entity type entity IDs with IDs in
   * indexing status table and returns only IDs of entities with unknown
   * indexing status. Those are considered to be unprocessed items.
   *
   * @param $entity_type_id
   * @param $bundle
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getUnprocessedItems($entity_type_id, $bundle) {
    if ($entity_type_instance = $this->entityTypeManager->getDefinition($entity_type_id, FALSE)) {
      if ($base_table = $entity_type_instance->getBaseTable()) {
        // Get entity ID key.
        $id_key = $entity_type_instance->getKey('id');

        // Construct query.
        $query = $this->database->select($base_table, 'e');
        $query->addField('e', $id_key, 'entity_id');
        $query->addExpression(sprintf("'%s'", $entity_type_id), 'entity_type');
        $query->leftJoin(IndexingStatusManager::DATABASE_TABLE, 'esis', "esis.id = e.{$id_key} AND esis.entity_type = :entity_type", [':entity_type' => $entity_type_id]);
        $query->isNull('esis.sid');

        if ($bundle) {
          // Get bundle key.
          $bundle_key = $entity_type_instance->getKey('bundle');
          // Add bundle condition.
          $query->condition('e.' . $bundle_key, $bundle);
        }

        return $query->execute()->fetchAll();
      }
    }

    return [];
  }

}
