<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
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
      '#type' => 'table',
      '#header' => $header,
      '#empty' => $this->t('No index plugins are defined.'),
    ];

    foreach ($this->elasticsearchIndexManager->getDefinitions() as $plugin) {
      $plugin_id = $plugin['id'];

      try {
        $index = Index::createFromPluginId($plugin_id);

        $row = [
          'label' => ['#markup' => (string) $index->getLabel()],
          'plugin_id' => ['#markup' => $index->getId()],
          'entity_type' => ['#markup' => $index->getEntityType() ?: '-'],
          'bundle' => ['#markup' => $index->getBundle() ?: '-'],
          'operations' => [
            'data' => [
              '#type' => 'operations',
              '#links' => $index->getOperations(),
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

      $form['listing'][$plugin_id] = $row;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
