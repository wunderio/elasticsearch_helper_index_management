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
    $t_args = [
      '@id' => $this->index->getId(),
      '%label' => $this->index->getLabel(),
    ];

    return $this->t('Indices managed by %label (@id) index plugin will be dropped.', $t_args);
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
    $this->index->getPluginInstance()->drop();

    parent::submitForm($form, $form_state);
  }

}
