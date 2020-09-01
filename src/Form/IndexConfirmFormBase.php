<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\elasticsearch_helper_index_management\Index;

/**
 * Class ConfirmFormBase
 */
abstract class IndexConfirmFormBase extends ConfirmFormBase {

  /**
   * @var \Drupal\elasticsearch_helper_index_management\Index
   */
  protected $index;

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->index->toUrl('view');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Routing\RouteMatchInterface $route_match */
    $route_match = $this->getRouteMatch();
    $this->index = new Index($route_match->getParameter('plugin'));

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
