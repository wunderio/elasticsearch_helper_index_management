<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SettingsForm
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * SettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);

    $this->config = $this->config('elasticsearch_helper_index_management.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'elasticsearch_helper_index_management.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elasticsearch_helper_index_management_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['defer_indexing'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Reindex in background'),
      '#description' => $this->t('Reindex content with a queue worker instead of Batch API. This can be useful when importing very large amounts of Drupal entities.'),
      '#default_value' => (int) $this->config->get('defer_indexing'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config->set('defer_indexing', (bool) $form_state->getValue('defer_indexing'));

    // Save submitted configuration values.
    $this->config->save();
  }

}
