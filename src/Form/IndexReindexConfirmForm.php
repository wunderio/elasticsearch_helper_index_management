<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Index re-indexing confirmation form.
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
    return $this->t('Are you sure you want to reindex the content?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Content items managed by the following index plugins will be queued for re-indexing:');
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['options'] = [
      '#title' => $this->t('Reindex options'),
      '#type' => 'details',
      '#open' => TRUE,
    ];

    $form['options']['mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Mode'),
      '#required' => TRUE,
      '#options' => [
        'simple' => $this->t('Simple reindex'),
        'failed_only' => $this->t('Simple reindex of failed items only'),
        'unprocessed_only' => $this->t('Simple reindex of unprocessed items only'),
        'complete' => $this->t('Complete reindex'),
      ],
      '#default_value' => 'simple',
      '#options_descriptions' => array(
        'simple' => 'Re-indexes all content items.',
        'failed_only' => 'Re-indexes only previously failed items.',
        'unprocessed_only' => 'Re-indexes only unprocessed items.',
        'complete' => 'Drops and creates the index, then re-indexes all content items.',
      ),
      '#after_build' => [[$this, 'buildOptionDescriptions']],
    ];

    return $form;
  }

  /**
   * Builds description for every option element.
   *
   * @param $element
   * @param $form_state
   *
   * @return array
   */
  public function buildOptionDescriptions($element, &$form_state) {
    foreach (Element::children($element) as $key) {
      $element[$key]['#description'] = $this->t('@description', [
        '@description' => $element['#options_descriptions'][$key],
      ]);
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get a queue.
    switch ($form_state->getValue('mode')) {
      case 'unprocessed_only':
        $queue = $this->queueFactory->get('elasticsearch_helper_index_management_reindex_unprocessed_only');
        break;

      case 'failed_only':
        $queue = $this->queueFactory->get('elasticsearch_helper_index_management_reindex_failed_only');
        break;

      case 'complete':
        $queue = $this->queueFactory->get('elasticsearch_helper_index_management_reindex_complete');
        break;

      default:
        $queue = $this->queueFactory->get('elasticsearch_helper_index_management_reindex');
    }

    // Create re-index queue item.
    foreach ($this->getIndexIds() as $plugin_id) {
      $queue->createItem(['plugin_id' => $plugin_id]);
    }

    $this->messenger()->addStatus('Content has been queued for re-indexing.');

    parent::submitForm($form, $form_state);
  }

}
