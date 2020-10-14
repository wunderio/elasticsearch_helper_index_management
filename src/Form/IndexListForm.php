<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager;
use Drupal\elasticsearch_helper_index_management\Index;
use Drupal\elasticsearch_helper_index_management\IndexingStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Index listing form.
 */
class IndexListForm extends FormBase {

  /**
   * @var \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager
   */
  protected $elasticsearchIndexManager;

  /**
   * @var \Drupal\elasticsearch_helper_index_management\IndexingStatusManager
   */
  protected $indexingStatusManager;

  /**
   * IndexController constructor.
   *
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager $elasticsearch_index_manager
   *   The index manager service.
   * @param \Drupal\elasticsearch_helper_index_management\IndexingStatusManagerInterface $indexing_status_manager
   *   The indexing status manager service.
   */
  public function __construct(ElasticsearchIndexManager $elasticsearch_index_manager, IndexingStatusManagerInterface $indexing_status_manager) {
    $this->elasticsearchIndexManager = $elasticsearch_index_manager;
    $this->indexingStatusManager = $indexing_status_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.elasticsearch_index.processor'),
      $container->get('elasticsearch_helper_index_management.indexing_status_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elasticsearch_helper_index_management_index_list_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $plugin = NULL) {
    // Define headers.
    $header = [
      $this->t('Label'),
      $this->t('Machine name'),
      $this->t('Entity type'),
      $this->t('Bundle'),
      $this->t('Indexed'),
      $this->t('Failed'),
      $this->t('Operations'),
    ];

    $form['listing'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#empty' => $this->t('No index plugins are defined.'),
    ];

    foreach ($this->elasticsearchIndexManager->getDefinitions() as $plugin) {
      $plugin_id = $plugin['id'];

      try {
        $index = Index::createFromPluginId($plugin_id);

        // Get operations.
        $operations = $index->getOperations();

        // Add destination to some operations.
        foreach ($operations as $operation_name => $operation) {
          // Do not add destination to view operation as this would
          // impede redirection on further form submissions.
          if (in_array($operation_name, ['view'])) {
            continue;
          }

          if (!isset($operation['query'])) {
            $operations[$operation_name]['query'] = [];
          }
          $operations[$operation_name]['query'] += $this->getDestinationArray();
        }

        $row = [
          (string) $index->getLabel(),
          $index->getId(),
          $index->getEntityType() ?: '-',
          $index->getBundle() ?: '-',
          $this->getSuccessfulCount($index),
          $this->getErrorCount($index),
          [
            'data' => [
              '#type' => 'operations',
              '#links' => $operations,
            ]
          ],
        ];
      }
      catch (PluginException $e) {
        $row = [
          [
            '#markup' => $this->t('Index plugin "@plugin_id" cannot be loaded.', ['@plugin_id' => $plugin_id]),
            '#wrapper_attributes' => ['colspan' => count($header)],
          ],
        ];
      }

      $form['listing']['#options'][$plugin_id] = $row;
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['reindex'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reindex'),
      '#op' => 'reindex',
      '#weight' => 10,
    ];

    $form['actions']['create'] = [
      '#type' => 'submit',
      '#value' => $this->t('Setup'),
      '#op' => 'setup',
      '#weight' => 20,
    ];

    $form['actions']['drop'] = [
      '#type' => 'submit',
      '#value' => $this->t('Drop indices'),
      '#op' => 'drop',
      '#button_type' => 'danger',
      '#weight' => 30,
    ];

    $form['generic_actions'] = [
      '#type' => 'actions',
    ];

    $form['generic_actions']['queue_clear'] = [
      '#type' => 'submit',
      '#value' => $this->t('Clear indexing queue'),
      '#description' => $this->t('Removes all current items from indexing queue.'),
      '#op' => 'reset',
      '#weight' => 10,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();

    if (isset($triggering_element['#op'])) {
      switch ($triggering_element['#op']) {
        case 'reindex':
          // Validate that the user has selected a plugin from the list.
          // If the plugin array is empty, it would throw an error in submit.
          if (empty(array_filter($form_state->getValue('listing')))) {
            $form_state->setErrorByName('reindex', 'Select a plugin to be re-indexed from the list');
          }
          break;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();

    if (isset($triggering_element['#op'])) {
      $plugin_ids = array_filter($form_state->getValue('listing'));

      $route_name = sprintf('elasticsearch_helper_index_management.index.%s', $triggering_element['#op']);
      $route_parameters = [
        'plugin' => implode(',', $plugin_ids),
      ];

      $url = Url::fromRoute($route_name, $route_parameters);
      $form_state->setRedirectUrl($url);
    }
  }

  /**
   * Returns successful items count.
   *
   * @param \Drupal\elasticsearch_helper_index_management\Index $index
   *
   * @return int
   */
  public function getSuccessfulCount(Index $index) {
    return $this->indexingStatusManager->count([
      'index_plugin' => $index->getPluginInstance()->getPluginId(),
      'status' => IndexingStatusManagerInterface::STATUS_SUCCESS,
    ]);
  }

  /**
   * Returns failed items count.
   *
   * @param \Drupal\elasticsearch_helper_index_management\Index $index
   *
   * @return int
   */
  public function getErrorCount(Index $index) {
    return $this->indexingStatusManager->count([
      'index_plugin' => $index->getPluginInstance()->getPluginId(),
      'status' => IndexingStatusManagerInterface::STATUS_ERROR,
    ]);
  }

}
