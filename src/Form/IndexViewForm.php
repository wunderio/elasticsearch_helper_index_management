<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\elasticsearch_helper_index_management\Index;

/**
 * Index overview form.
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
  public function buildForm(array $form, FormStateInterface $form_state, $plugin = []) {
    // View only the first plugin.
    $plugin = reset($plugin);
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

    try {
      $existing_indices = $index->getPluginInstance()->getExistingIndices();
    }
    catch (\Throwable $e) {
      $existing_indices = [];
    }

    $rows['existing_index_names'] = [
      [
        'data' => [
          '#type' => 'inline_template',
          '#template' => '<span class="title">{{ title }}</span><div class="description"><em>{{ description }}</em></div>',
          '#context' => [
            'title' => $this->t('Existing indices managed by the plugin'),
            'description' => $this->t('Note: Index names matching the index name pattern are considered to be managed by the plugin.'),
          ],
        ],
      ],
      [
        'data' => $existing_indices ? [
          '#theme' => 'item_list',
          '#items' => $existing_indices,
        ] : '-',
      ],
    ];

    $form['overview'] = [
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
      '#value' => $this->t('Drop index'),
      '#op' => 'drop',
      '#button_type' => 'danger',
      '#weight' => 30,
    ];

    return $form;
  }

  /**
   * Returns title for the view page.
   *
   * @param \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface[] $plugin
   *
   * @return \Drupal\Component\Render\MarkupInterface|string
   */
  public function viewTitle($plugin = []) {
    // View only the first plugin.
    $plugin = reset($plugin);
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // View only the first plugin.
    /** @var \Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexInterface[] $plugin */
    $plugin = $form_state->getBuildInfo()['args'][0];
    $plugin = reset($plugin);

    $index = new Index($plugin);
    $triggering_element = $form_state->getTriggeringElement();

    if (isset($triggering_element['#op'])) {
      $url = $index->toUrl($triggering_element['#op'], []);
      $form_state->setRedirectUrl($url);
    }
  }

}
