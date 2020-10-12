<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Queue clear confirmation form.
 */
class QueueClearConfirmForm extends IndexConfirmFormBase {

  /**
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected $indexingQueue;

  /**
   * ReindexConfirmForm constructor.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   Query factory service.
   */
  public function __construct(QueueFactory $queue_factory) {
    $this->indexingQueue = $queue_factory->get('elasticsearch_helper_indexing');
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
    return $this->t('Are you sure you want to clear the indexing queue?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $t_args = ['@count' => $this->indexingQueue->numberOfItems()];

    return $this->t('Any pending indexing queue items will be removed. Currently there are @count items in the indexing queue.', $t_args);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elasticsearch_helper_index_management_index_queue_clear_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->indexingQueue->deleteQueue();

    $this->messenger()->addStatus('Indexing queue has been cleared.');

    parent::submitForm($form, $form_state);
  }

}
