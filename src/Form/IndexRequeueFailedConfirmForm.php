<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\elasticsearch_helper_index_management\IndexItemManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class IndexRequeueFailedConfirmForm
 */
class IndexRequeueFailedConfirmForm extends IndexConfirmFormBase {

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
    return $this->t('Are you sure you want to re-queue all failed items for re-indexing?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('All previously indexed items which failed during processing will be queued for re-indexing:');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elasticsearch_helper_index_management_index_requeue_failed_confirm_form';
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
    $queue = $this->queueFactory->get('elasticsearch_helper_indexing');

    if ($plugin = $form_state->get('plugin')) {
      $plugin = is_array($plugin) ? reset($plugin) : $plugin;
      $definition = $plugin->getPluginDefinition();

      if (isset($definition['entityType'])) {
        $items = $this
          ->indexItemManager
          ->getAll(['entity_type' => $definition['entityType']]);

        foreach ($items as $item) {
          $queue->createItem([
            'entity_type' => $item->entity_type,
            'entity_id' => $item->entity_id,
          ]);
        }

        // Clear the records.
        $this
          ->indexItemManager
          ->clear(['entity_type' => $definition['entityType']]);
      }
    }

    $this->messenger()->addStatus('Content has been queued for re-indexing.');

    parent::submitForm($form, $form_state);
  }

}
