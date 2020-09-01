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
   */
  public function convert($value, $definition, $name, array $defaults) {
    try {
      $result = $this->elasticsearchIndexManager->createInstance($defaults['plugin']);
    }
    catch (\Exception $e) {
      throw new ParamNotConvertedException(sprintf('Index plugin %s could not be loaded.', $defaults['plugin']));
    }

    // Set to NULL if object could not be loaded. This will trigger
    // an exception to be thrown and 404 page displayed.
    return $result ? $result : NULL;
  }

}
