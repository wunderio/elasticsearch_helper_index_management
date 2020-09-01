<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\elasticsearch_helper_index_management\ElasticsearchQueueManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class QueueClearConfirmForm
 */
class QueueClearConfirmForm extends ConfirmFormBase {

  /**
   * @var \Drupal\elasticsearch_helper_index_management\ElasticsearchQueueManager
   */
  protected $elasticsearchQueueManager;

  /**
   * @var string
   */
  protected $indexPluginId;

  /**
   * QueueClearConfirmForm constructor.
   *
   * @param \Drupal\elasticsearch_helper_index_management\ElasticsearchQueueManagerInterface $elasticsearch_queue_manager
   */
  public function __construct(ElasticsearchQueueManagerInterface $elasticsearch_queue_manager) {
    $this->elasticsearchQueueManager = $elasticsearch_queue_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('elasticsearch_helper_index_management.queue_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to clear all queue items?');
  }

  public function getDescription() {
    $t_args = ['@plugin_id' => $this->indexPluginId];

    return $this->t('Items managed by "@plugin_id" index plugin will be cleared from the queue.', $t_args);
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
    return 'elasticsearch_helper_index_management_queue_create_confirm_form';
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
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->elasticsearchQueueManager->clear($this->indexPluginId);

    $form_state->setRedirectUrl($this->getCancelUrl());
  }


}
