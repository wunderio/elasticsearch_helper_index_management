<?php

namespace Drupal\elasticsearch_helper_index_management;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Views data provider for index management module.
 */
class ViewsData implements ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ViewsData constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Provides views data for index management tables.
   */
  public function getViewsData() {
    $data = [];

    $data['elasticsearch_helper_indexing_status']['table']['group'] = t('Elasticsearch indexing status');

    $data['elasticsearch_helper_indexing_status']['table']['base'] = [
      'field' => 'sid',
      'title' => t('Elasticsearch indexing status'),
      'help' => t('Elasticsearch indexing status table.'),
      'query_id' => 'views_query',
    ];

    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      $base_table = $entity_type->getDataTable() ?: $entity_type->getBaseTable();
      $id_key = $entity_type->getKey('id');

      $data[$base_table]['elasticsearch_helper_indexing_status'] = [
        'title' => t('Indexing status'),
        'relationship' => [
          'group' => t('Elasticsearch Helper'),
          'label' => t('Indexing status'),
          'help' => t('Indexing status'),
          'base' => 'elasticsearch_helper_indexing_status',
          'base field' => 'id',
          'relationship field' => $id_key,
          'id' => 'standard',
          'extra' => [
            [
              'field' => 'entity_type',
              'value' => $entity_type_id,
            ],
          ],
        ],
      ];
    }

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
