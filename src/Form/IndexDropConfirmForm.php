<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class IndexDropConfirmForm
 */
class IndexDropConfirmForm extends IndexConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to drop indices?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Indices managed by the following index plugins will be dropped:');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elasticsearch_helper_index_management_index_drop_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($this->indices as $index) {
      $index->getPluginInstance()->drop();
    }

    $this->messenger()->addStatus('Indices have been dropped.');

    parent::submitForm($form, $form_state);
  }

}
