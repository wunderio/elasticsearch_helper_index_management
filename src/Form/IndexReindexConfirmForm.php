<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class IndexReindexConfirmForm
 */
class IndexReindexConfirmForm extends IndexConfirmFormBase {

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
    return $this->t('All content items managed by the following index plugins will be queued for re-indexing:');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elasticsearch_helper_index_management_index_reindex_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get a queue.
    $queue = $this->queueFactory->get('elasticsearch_helper_index_management_reindex');

    // Create re-index queue item.
    foreach ($this->getIndexIds() as $plugin_id) {
      $queue->createItem(['plugin_id' => $plugin_id]);
    }

    $this->messenger()->addStatus('Content has been queued for re-indexing.');

    parent::submitForm($form, $form_state);
  }

}
