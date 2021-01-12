<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\elasticsearch_helper_index_management\Index;

/**
 * Index Create Version confirmation class.
 */
class CreateVersionConfirmForm extends IndexConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to create a new version of the index?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('A new version of the index will be created. The new index will not contain any documents, and it needs to be activated.');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elasticsearch_helper_index_management_index_create_version_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\elasticsearch_helper_index_management\Index $index */
    $index = reset($this->indices);

    /** @var \Drupal\elasticsearch_helper_index_management\IndexVersionManager $version_manager */
    $version_manager = \Drupal::service('elasticsearch_helper_index_management.index_version_manager');
    if ($version_manager->incrementVersion($index->getId())) {
      $this->messenger()->addStatus('A new version of the index has been created.');
    }
    else {
      $this->messenger()->addError('Index version cannot be created. Check the logs for error');
    }

    parent::submitForm($form, $form_state);
  }

}
