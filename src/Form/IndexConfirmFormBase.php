<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\elasticsearch_helper_index_management\Index;

/**
 * Abstract class for index confirmation forms.
 */
abstract class IndexConfirmFormBase extends ConfirmFormBase {

  /**
   * @var \Drupal\elasticsearch_helper_index_management\Index[]
   */
  protected $indices = [];

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    // If confirmation form deals only with one index, return to the index
    // plugin view page.
    if (count($this->indices) == 1) {
      $index = reset($this->indices);

      return $index->toUrl('view');
    }
    // Otherwise return to the list of index plugins.
    else {
      return new Url('elasticsearch_helper_index_management.index.list');
    }
  }

  /**
   * Returns a list of index plugin IDs.
   *
   * @return string[]
   */
  public function getIndexIds() {
    return array_map(function ($index) {
      return $index->getId();
    }, $this->indices);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $route_match = $this->getRouteMatch();

    /** @var \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $index_plugin */
    foreach ($route_match->getParameter('plugin') as $index_plugin) {
      $this->indices[] = new Index($index_plugin);
    }

    // Get parent form.
    $form = parent::buildForm($form, $form_state);

    // Get list of index plugin labels and IDs.
    $index_names = array_map(function ($index) {
      return sprintf('%s (%s)', $index->getLabel(), $index->getId());
    }, $this->indices);

    $form['list'] = [
      '#theme' => 'item_list',
      '#items' => $index_names,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
