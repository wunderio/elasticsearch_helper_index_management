<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Url;
use Drupal\elasticsearch_helper_index_management\ElasticsearchBatchManager;
use Drupal\elasticsearch_helper_index_management\ElasticsearchQueueManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class QueueProcessConfirmForm
 */
class QueueProcessConfirmForm extends ConfirmFormBase {

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * @var \Drupal\elasticsearch_helper_index_management\ElasticsearchQueueManager
   */
  protected $elasticsearchQueueManager;

  /**
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * @var string
   */
  protected $indexPluginId;

  /**
   * QueueProcessConfirmForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\elasticsearch_helper_index_management\ElasticsearchQueueManagerInterface $elasticsearch_queue_manager
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory, ElasticsearchQueueManagerInterface $elasticsearch_queue_manager, QueueFactory $queue_factory) {
    $this->config = $config_factory->get('elasticsearch_helper_index_management.settings');
    $this->elasticsearchQueueManager = $elasticsearch_queue_manager;
    $this->queueFactory = $queue_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('elasticsearch_helper_index_management.queue_manager'),
      $container->get('queue')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to process all the queued items?');
  }

  public function getDescription() {
    $t_args = ['@plugin_id' => $this->indexPluginId];

    if ($this->deferIndexing()) {
      return $this->t('Items managed by "@plugin_id" index plugin will be queued and executed on next cron run.', $t_args);
    }
    else {
      return $this->t('Items managed by "@plugin_id" index plugin will be indexed using Batch API.', $t_args);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $route_params = ['index_id' => $this->indexPluginId];

    return Url::fromRoute('elasticsearch_helper_index_management.index_status', $route_params);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elasticsearch_helper_index_management_queue_process_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Routing\RouteMatchInterface $route_match */
    $route_match = $this->getRouteMatch();
    $this->indexPluginId = $route_match->getParameter('index_id');

    return parent::buildForm($form, $form_state);
  }

  /**
   * Returns TRUE if indexing is deferred.
   *
   * @return bool
   */
  public function deferIndexing() {
    return $this->config->get('defer_indexing');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get items from processing.
    $items = $this->elasticsearchQueueManager->getItems($this->indexPluginId);

    if ($this->deferIndexing()) {
      $queue = $this->queueFactory->get('elasticsearch_helper_index_management_reindex');

      // Create queue items.
      foreach ($items as $item) {
        if (!$item->status) {
          $queue->createItem([
            'callback' => ElasticsearchBatchManager::class . '::processOne',
            'params' => [$item->id]
          ]);
        }
      }
    }
    else {
      // Create a batch for processing re-indexing.
      $batch = [
        'title' => $this->t('Re-indexing @id', ['@id' => $this->indexPluginId]),
        'operations' => [],
        'init_message' => $this->t('Starting'),
        'progress_message' => $this->t('Processed @current out of @total.'),
        'error_message' => $this->t('An error occurred during processing'),
        'finished' => ElasticsearchBatchManager::class . '::processFinished',
      ];

      foreach ($items as $item) {
        if (!$item->status) {
          $batch['operations'][] = [ElasticsearchBatchManager::class . '::processOne', [$item->id]];
        }
      }

      batch_set($batch);
    }

    $form_state->setRedirectUrl($this->getCancelUrl());
  }


}
