<?php

namespace Drupal\elasticsearch_helper_index_management\Controller;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager;
use Drupal\elasticsearch_helper_index_management\Index;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class IndexController
 */
class IndexController extends ControllerBase {

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
   * List index plugins.
   *
   * @return array
   */
  public function listing() {
    // Define headers.
    $header = [
      $this->t('Label'),
      $this->t('Machine name'),
      $this->t('Entity type'),
      $this->t('Bundle'),
      $this->t('Operations'),
    ];

    // Build rows.
    $rows = [];

    foreach ($this->elasticsearchIndexManager->getDefinitions() as $plugin) {
      $plugin_id = $plugin['id'];

      try {
        $index = Index::createFromPluginId($plugin_id);

        $operations = $index->getOperations();

        foreach ($operations as &$operation) {
          if (!isset($operation['query'])) {
            $operation['query'] = [];
          }
          $operation['query'] += $this->getDestinationArray();
        }

        $row = [
          'label' => (string) $index->getLabel(),
          'plugin_id' => $index->getId(),
          'entity_type' => $index->getEntityType() ?: '-',
          'bundle' => $index->getBundle() ?: '-',
          'operations' => [
            'data' => [
              '#type' => 'operations',
              '#links' => $operations,
            ]
          ],
        ];
      }
      catch (PluginException $e) {
        $row = [
          '#markup' => $this->t('Index plugin "@plugin_id" cannot be loaded.', ['@plugin_id' => $plugin_id]),
          '#wrapper_attributes' => ['colspan' => count($header)],
        ];
      }

      $rows[] = $row;
    }

    $build = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No index plugins are defined.'),
    ];

    return $build;
  }

  /**
   * Returns index plugin status.
   *
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $plugin
   *
   * @return array
   */
  public function view(ElasticsearchIndexInterface $plugin) {
    $index = new Index($plugin);

    $rows = [
      'label' => [
        $this->t('Label'),
        $index->getLabel(),
      ],
      'plugin_id' => [
        $this->t('Plugin ID'),
        $index->getId(),
      ],
      'entity_type' => [
        $this->t('Entity type'),
        $index->getEntityType() ?: '-',
      ],
      'bundle' => [
        $this->t('Bundle'),
        $index->getBundle() ?: '-',
      ],
      'index_name' => [
        $this->t('Index name pattern'),
        $index->getPluginInstance()->getPluginDefinition()['indexName'],
      ],
    ];

    $build['overview'] = [
      '#type' => 'table',
      '#header' => [
        [
          'data' => [
            '#markup' => $this->t('Overview'),
          ],
          'colspan' => 2,
        ],
      ],
      '#rows' => $rows,
    ];

    $build['actions'] = [
      '#type' => 'actions',
    ];

    $build['actions']['reindex'] = [
      '#type' => 'link',
      '#title' => $this->t('Reindex'),
      '#url' => $index->toUrl('reindex'),
      '#attributes' => [
        'class' => ['button'],
      ],
    ];

    $build['actions']['create'] = [
      '#type' => 'link',
      '#title' => $this->t('Setup'),
      '#url' => $index->toUrl('setup'),
      '#attributes' => [
        'class' => ['button'],
      ],
    ];

    $build['actions']['delete'] = [
      '#type' => 'link',
      '#title' => $this->t('Delete index'),
      '#url' => $index->toUrl('delete'),
      '#attributes' => [
        'class' => ['button', 'button--danger'],
      ],
    ];

    return $build;
  }

  /**
   * Returns title for the view page.
   *
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $plugin
   *
   * @return \Drupal\Component\Render\MarkupInterface|string
   */
  public function viewTitle(ElasticsearchIndexInterface $plugin) {
    $index = new Index($plugin);

    $t_args = [
      '@id' => $index->getId(),
      '%label' => $index->getLabel(),
    ];

    return $this->t('Index plugin %label (@id)', $t_args);
  }

}
