<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface;
use Drupal\elasticsearch_helper_index_management\Index;

/**
 * Class IndexViewForm
 */
class IndexViewForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elasticsearch_helper_index_management_index_view_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $plugin = NULL) {
    $index = new Index($plugin);

    $rows['label'] = [
      $this->t('Label'),
      $index->getLabel(),
    ];

    $rows['plugin_id'] = [
      $this->t('Plugin ID'),
      $index->getId(),
    ];

    $rows['entity_type'] = [
      $this->t('Entity type'),
      $index->getEntityType() ?: '-',
    ];

    $rows['bundle'] = [
      $this->t('Bundle'),
      $index->getBundle() ?: '-',
    ];

    $rows['index_name'] = [
      $this->t('Index name pattern'),
      $index->getPluginInstance()->getPluginDefinition()['indexName'],
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
      '#type' => 'submit',
      '#value' => $this->t('Reindex'),
      '#op' => 'reindex',
      '#weight' => 10,
    ];

    $build['actions']['create'] = [
      '#type' => 'submit',
      '#value' => $this->t('Setup'),
      '#op' => 'setup',
      '#weight' => 20,
    ];

    $build['actions']['drop'] = [
      '#type' => 'submit',
      '#value' => $this->t('Drop index'),
      '#op' => 'drop',
      '#button_type' => 'danger',
      '#weight' => 30,
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

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state, $plugin = NULL) {
    /** @var \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface $plugin */
    $plugin = $form_state->getBuildInfo()['args'][0];

    $index = new Index($plugin);
    $triggering_element = $form_state->getTriggeringElement();

    if (isset($triggering_element['#op'])) {
      $url = $index->toUrl($triggering_element['#op']);
      $form_state->setRedirectUrl($url);
    }
  }
}
