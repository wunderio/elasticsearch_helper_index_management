<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\elasticsearch_helper_index_management\IndexItemManager;
use Drupal\elasticsearch_helper_index_management\IndexItemManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ResetConfirmForm
 */
class ResetConfirmForm extends IndexConfirmFormBase {

  /**
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * @var \Drupal\elasticsearch_helper_index_management\IndexItemManager
   */
  protected $indexItemManager;

  /**
   * ReindexConfirmForm constructor.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   Query factory service.
   * @param \Drupal\elasticsearch_helper_index_management\IndexItemManagerInterface $index_item_manager
   *   Index item manager service.
   */
  public function __construct(QueueFactory $queue_factory, IndexItemManagerInterface $index_item_manager) {
    $this->queueFactory = $queue_factory;
    $this->indexItemManager = $index_item_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('queue'),
      $container->get('elasticsearch_helper_index_management.index_item_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to reset all indexing related queue operations?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Any pending queue operation and failed items will be cleared and reset.');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elasticsearch_helper_index_management_index_reset_confirm_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $plugin = '') {
    $form_state->set('plugin', $plugin);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Reset failed items queue.
    $this->indexItemManager->clear(['flag' => IndexItemManager::FLAG_FAIL]);

    // Reset indexing queue.
    $queue = $this->queueFactory->get('elasticsearch_helper_indexing');
    $queue->deleteQueue();

    $this->messenger()->addStatus('Queue operations has been reset');

    parent::submitForm($form, $form_state);
  }

}
