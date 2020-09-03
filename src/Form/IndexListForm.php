<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager;
use Drupal\elasticsearch_helper_index_management\Index;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class IndexListForm
 */
class IndexListForm extends FormBase {

  /**
   * @var \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager
   */
  protected $elasticsearchIndexManager;

  /**
   * IndexController constructor.
   *
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager $elasticsearch_index_manager
   */
  public function __construct(ElasticsearchIndexManager $elasticsearch_index_manager) {
    $this->elasticsearchIndexManager = $elasticsearch_index_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.elasticsearch_index.processor')
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

    return $form;
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

}
