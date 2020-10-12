<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Index setup confirmation form.
 */
class IndexSetupConfirmForm extends IndexConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to create indices?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t("Indices managed by the following index plugins will be created only if they don't exist.");
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elasticsearch_helper_index_management_index_setup_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($this->indices as $index) {
      $index->getPluginInstance()->setup();
    }

    $this->messenger()->addStatus('Indices have been created.');

    parent::submitForm($form, $form_state);
  }

}
