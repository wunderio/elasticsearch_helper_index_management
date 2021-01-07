<?php

namespace Drupal\elasticsearch_helper_index_management;

/**
 * Views data provider for index management module.
 */
class ViewsData {

  /**
   * Provides views data for index management tables.
   */
  public function getViewsData() {
    $data = [];

    $data['elasticsearch_helper_indexing_status']['table']['group'] = t('Elasticsearch indexing');

    $data['elasticsearch_helper_indexing_status']['table']['base'] = [
      'field' => 'sid',
      'title' => t('Elasticsearch indexing status'),
      'help' => t('Elasticsearch indexing status table.'),
      'query_id' => 'views_query',
    ];

    $data['elasticsearch_helper_indexing_status']['sid'] = [
      'title' => t('Status entry ID'),
      'help' => t('Indexing status entry ID.'),
      'field' => [
        'id' => 'numeric',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
    ];

    $data['elasticsearch_helper_indexing_status']['index_plugin'] = [
      'title' => t('Index plugin'),
      'help' => t('Index plugin the indexing status relates to.'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'string',
      ],
    ];

    $data['elasticsearch_helper_indexing_status']['status'] = [
      'title' => t('Status'),
      'help' => t('Indexing status.'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'string',
      ],
    ];

    $data['elasticsearch_helper_indexing_status']['id'] = [
      'title' => t('Entity or object ID'),
      'help' => t('Entity or index-able object ID.'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'string',
      ],
    ];

    $data['elasticsearch_helper_indexing_status']['entity_type'] = [
      'title' => t('Entity type'),
      'help' => t('Entity type of the entity.'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'string',
      ],
    ];

    $data['elasticsearch_helper_indexing_status']['created'] = [
      'title' => t('Index time'),
      'help' => t('Timestamp when indexing was attempted.'),
      'field' => [
        'id' => 'date',
      ],
      'filter' => [
        'id' => 'date',
      ],
      'sort' => [
        'id' => 'date',
      ],
    ];

    return $data;
  }

}
