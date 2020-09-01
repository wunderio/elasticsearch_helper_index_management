<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ReindexConfirmForm
 */
class ReindexConfirmForm extends IndexConfirmFormBase {

  /**
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * ReindexConfirmForm constructor.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   */
  public function __construct(QueueFactory $queue_factory) {
    $this->queueFactory = $queue_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('queue')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to reindex all items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $t_args = ['@plugin_id' => $this->index->getId()];

    return $this->t('All content items managed by "@plugin_id" index plugin will be queued for re-indexing.', $t_args);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elasticsearch_helper_index_management_reindex_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get a queue.
    $queue = $this->queueFactory->get('elasticsearch_helper_index_management_reindex');

    // Create re-index queue item.
    $queue->createItem(['plugin_id' => $this->index->getId()]);

    parent::submitForm($form, $form_state);
  }

}
