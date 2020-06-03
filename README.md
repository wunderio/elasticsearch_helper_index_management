# Elasticsearch Helper Index management

`elasticsearch_helper_index_management` is a module that provides managing re-indexes from the administrative UI and allows re-indexing of failed items.
## Requirements

* Drupal 8 or Drupal 9
* [Elasticsearch Helper][elasticsearch_helper] module

## Installation

Elasticsearch Helper Index management can be installed via the
[standard Drupal installation process](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).

## Configuration

* Install and enable [Elasticsearch Helper][elasticsearch_helper] module.
* Install and enable [Elasticsearch Helper Index management][elasticsearch_helper_index_management]
  module.
* Go to the `admin/config/search/elasticsearch_helper/index_management/indices` to manage re-indexes

## Usage

1. Install `Elasticsearch` search engine ([how-to][elasticsearch_download]).
2. Install [`Elasticsearch Helper`][elasticsearch_helper] module, configure it and create indices using `ElasticsearchIndex` type plugins in a custom module.
3. Go to the `admin/config/search/elasticsearch_helper/index_management/indices` to manage re-indexes.
4. To manage your index, select Manage from Actions
5. With each index options are:
   - Queue all documents in the index for re-indexing
   - Remove all items from the queue.
   - Batch process all queued items.

[elasticsearch_download]: https://www.elastic.co/downloads/elasticsearch
[elasticsearch_helper]: https://www.drupal.org/project/elasticsearch_helper
[elasticsearch_helper_index_management]: https://www.drupal.org/project/elasticsearch_helper_index_management
[elasticsearch_client]: https://github.com/elastic/elasticsearch-php
