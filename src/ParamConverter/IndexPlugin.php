<?php

namespace Drupal\elasticsearch_helper_index_management\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Drupal\Core\ParamConverter\ParamNotConvertedException;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager;
use Symfony\Component\Routing\Route;

/**
 * Class IndexPlugin
 */
class IndexPlugin implements ParamConverterInterface {

  /**
   * @var \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager
   */
  protected $elasticsearchIndexManager;

  /**
   * IndexPlugin constructor.
   *
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexManager $elasticsearch_index_manager
   */
  public function __construct(ElasticsearchIndexManager $elasticsearch_index_manager) {
    $this->elasticsearchIndexManager = $elasticsearch_index_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return !empty($definition['type']) && $definition['type'] == 'elasticsearch_index_plugin';
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\ParamConverter\ParamNotConvertedException
   *
   * @return \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface[]
   */
  public function convert($value, $definition, $name, array $defaults) {
    $plugin_ids = explode(',', $defaults['plugin']);
    array_walk($plugin_ids, 'trim');

    try {
      $result = [];

      foreach ($plugin_ids as $plugin_id) {
        $result[] = $this->elasticsearchIndexManager->createInstance($plugin_id);
      }
    }
    catch (\Exception $e) {
      throw new ParamNotConvertedException(sprintf('Index plugin %s could not be loaded.', $plugin_id));
    }

    // Set to NULL if object could not be loaded. This will trigger
    // an exception to be thrown and 404 page displayed.
    return $result ? $result : NULL;
  }

}
