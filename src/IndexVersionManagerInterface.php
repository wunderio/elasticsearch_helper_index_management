<?php

namespace Drupal\elasticsearch_helper_index_management;

/**
 * Defines Index Version Manager Interface.
 */
interface IndexVersionManagerInterface {

  /**
   * Get the current version of the index plugin.
   *
   * @param string $plugin_id
   *   The index plugin id.
   *
   * @return int
   *   Version number or FALSE if not initialized.
   */
  public function getCurrentVersion($plugin_id);

}
