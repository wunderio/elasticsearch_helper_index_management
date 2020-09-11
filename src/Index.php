<?php

namespace Drupal\elasticsearch_helper_index_management;

use Drupal\Core\Url;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface;

/**
 * A wrapper around Elasticsearch index plugin instance.
 */
class Index {

  /**
   * @var \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface
   */
  protected $indexPlugin;

  /**
   * Index constructor.
   *
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $index_plugin
   */
  public function __construct(ElasticsearchIndexInterface $index_plugin) {
    $this->indexPlugin = $index_plugin;
  }

  /**
   * Returns instance of self.
   *
   * @param $plugin_id
   *
   * @return static
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public static function createFromPluginId($plugin_id) {
    /** @var \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager $elasticsearch_index_manager */
    $elasticsearch_index_manager = \Drupal::service('plugin.manager.elasticsearch_index.processor');

    /** @var \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $plugin */
    $plugin = $elasticsearch_index_manager->createInstance($plugin_id);

    return new static($plugin);
  }

  /**
   * Returns plugin instance.
   *
   * @return \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface
   */
  public function getPluginInstance() {
    return $this->indexPlugin;
  }

  /**
   * Returns index plugin ID.
   *
   * @return string
   */
  public function getId() {
    return $this->indexPlugin->getPluginId();
  }

  /**
   * Returns index plugin label.
   *
   * @return \Drupal\Component\Render\MarkupInterface|string
   */
  public function getLabel() {
    return $this->indexPlugin->getPluginDefinition()['label'];
  }

  /**
   * Returns entity type that index plugin manages.
   *
   * @return string|null
   */
  public function getEntityType() {
    $definition = $this->indexPlugin->getPluginDefinition();

    return isset($definition['entityType']) ? $definition['entityType'] : NULL;
  }

  /**
   * Returns entity type bundle that index plugin manages.
   *
   * @return string|null
   */
  public function getBundle() {
    $definition = $this->indexPlugin->getPluginDefinition();

    return isset($definition['bundle']) ? $definition['bundle'] : NULL;
  }

  /**
   * Gets the URL object for the index.
   *
   * Relations: list, canonical, edit, delete.
   *
   * @param string $rel
   * @param array $route_parameters
   * @param array $options
   *
   * @return \Drupal\Core\Url
   */
  public function toUrl($rel = 'view', $route_parameters = [], array $options = []) {
    // Prepare route parameters.
    $route_parameters = $route_parameters + $this->getRouteParams();

    return Url::fromRoute(
      sprintf('elasticsearch_helper_index_management.index.%s', $rel),
      $route_parameters,
      $options
    );
  }

  /**
   * Returns a list of parameters to use in routes.
   *
   * @return array
   */
  public function getRouteParams() {
    return [
      'plugin' => $this->indexPlugin->getPluginId(),
    ];
  }

  /**
   * Returns default operations that can be performed on the index.
   *
   * @return array
   */
  protected function getDefaultOperations() {
    $operations['view'] = [
      'title' => t('View'),
      'weight' => 70,
      'url' => $this->toUrl('view'),
    ];

    $operations['reindex'] = [
      'title' => t('Reindex'),
      'weight' => 90,
      'url' => $this->toUrl('reindex'),
    ];

    $operations['setup'] = [
      'title' => t('Setup'),
      'weight' => 110,
      'url' => $this->toUrl('setup'),
    ];

    $operations['drop'] = [
      'title' => t('Drop'),
      'weight' => 130,
      'url' => $this->toUrl('drop'),
    ];

    return $operations;
  }

  /**
   * Returns a list of operations.
   *
   * @return array
   */
  public function getOperations() {
    $module_handler = \Drupal::moduleHandler();
    $plugin = $this->getPluginInstance();

    $operations = $this->getDefaultOperations();
    $operations += $module_handler->invokeAll('elasticsearch_helper_index_operation', [$plugin]);
    $module_handler->alter('elasticsearch_helper_index_operation', $operations, $plugin);
    uasort($operations, '\Drupal\Component\Utility\SortArray::sortByWeightElement');

    if (!empty($operations_return)) {
      $operations = array_intersect_key($operations, array_flip($operations_return));
    }

    return $operations;
  }

}
