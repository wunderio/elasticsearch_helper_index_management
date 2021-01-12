<?php

namespace Drupal\elasticsearch_helper_index_management\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Index Revert Version confirmation class.
 */
class RevertVersionConfirmForm extends IndexConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to revert to an older version of the index?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Warning: The older version might not contain up to date information. Reindexing is adviced.');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elasticsearch_helper_index_management_index_revert_version_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\elasticsearch_helper_index_management\Index $index */
    $index = reset($this->indices);

    /** @var \Drupal\elasticsearch_helper_index_management\IndexVersionManager $version_manager */
    $version_manager = \Drupal::service('elasticsearch_helper_index_management.index_version_manager');
    if ($version_manager->decrementVersion($index->getId())) {
      $this->messenger()->addStatus('The index version has been reverted.');
    }
    else {
      $this->messenger()->addError('Index version cannot be reverted. Check the logs for error');
    }

    parent::submitForm($form, $form_state);
  }

}
