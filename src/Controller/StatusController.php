<?php

namespace Drupal\elasticsearch_helper_index_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager;
use Drupal\elasticsearch_helper_index_management\ElasticsearchQueueManagerInterface;

/**
 * Class StatusController.
 */
class StatusController extends ControllerBase {

  /**
   * @var \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager
   */
  protected $elasticsearchHelperPluginManager;

  /**
   * @var \Drupal\elasticsearch_helper_index_management\ElasticsearchQueueManager
   */
  protected $elasticsearchQueueManager;

  /**
   * StatusController constructor.
   *
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager $elasticsearch_plugin_manager
   * @param \Drupal\elasticsearch_helper_index_management\ElasticsearchQueueManagerInterface $elasticsearch_queue_manager
   */
  public function __construct(ElasticsearchIndexManager $elasticsearch_plugin_manager, ElasticsearchQueueManagerInterface $elasticsearch_queue_manager) {
    $this->elasticsearchHelperPluginManager = $elasticsearch_plugin_manager;
    $this->elasticsearchQueueManager = $elasticsearch_queue_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.elasticsearch_index.processor'),
      $container->get('elasticsearch_helper_index_management.queue_manager')
    );
  }

  /**
   * Display current re-index status.
   *
   * @param string $index_id
   *   The index plugin id.
   *
   * @return array
   *   Status markup.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function status($index_id) {
    $definition = $this->elasticsearchHelperPluginManager->getDefinition($index_id);

    $status = $this->elasticsearchQueueManager->getStatus($definition['id']);

    if ($status['total']) {
      $status_text = $status['processed'] . '/' . $status['total'] . ' items processed';

      if ($status['errors']) {
        $status_text .= ' (' . $status['errors'] . ' items not indexed due to errors)';
      }
    }
    else {
      $status_text = 'There are currently no items queued for re-indexing';
    }

    $rows = [
      ['Index', $definition['label'] . ' (' . $definition['id'] . ')'],
      ['Entity Type', $definition['entityType']],
      ['Status', $status_text],
    ];

    $build['table'] = [
      '#type' => 'table',
      '#rows' => $rows,
    ];

    $build['actions'] = [];

    $action_link_class = ['button', 'button--small'];

    if ($status['total'] > 0) {
      $create_link_title = $this->t('Update re-index queue');
    }
    else {
      $create_link_title = $this->t('Create re-index queue');
    }

    $build['actions']['queue_create'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('elasticsearch_helper_index_management.queue_create', ['index_id' => $index_id]),
      '#title' => $create_link_title,
      '#attributes' => ['class' => $action_link_class],
    ];

    $build['actions']['queue_clear'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('elasticsearch_helper_index_management.queue_clear', ['index_id' => $index_id]),
      '#title' => $this->t('Remove all items from the queue'),
      '#attributes' => ['class' => $action_link_class],
      '#access' => !empty($status['total']),
    ];

    $build['actions']['queue_process'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('elasticsearch_helper_index_management.queue_process', ['index_id' => $index_id]),
      '#title' => $this->t('Process all queue items'),
      '#attributes' => ['class' => $action_link_class],
      '#access' => !empty($status['total']),
    ];

    return $build;

  }

}
