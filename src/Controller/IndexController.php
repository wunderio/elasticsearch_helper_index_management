<?php

namespace Drupal\elasticsearch_helper_index_management\Controller;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Controller\ControllerBase;
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

        $row = [
          'label' => (string) $index->getLabel(),
          'plugin_id' => $index->getId(),
          'entity_type' => $index->getEntityType() ?: '-',
          'bundle' => $index->getBundle() ?: '-',
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

}
