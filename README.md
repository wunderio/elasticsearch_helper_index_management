# Elasticsearch Helper Index management

`elasticsearch_helper_index_management` is a module that provides managing re-indexes from the administrative UI and allows re-indexing of failed items.
## Requirements

* Drupal 8 or Drupal 9
* [Elasticsearch Helper][elasticsearch_helper] module

## Installation

Elasticsearch Helper Index management can be installed via the
[standard Drupal installation process](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).

1. Install `Elasticsearch` search engine ([how-to][elasticsearch_download]).
2. Install and enable [Elasticsearch Helper][elasticsearch_helper] module.
3. Install and enable [Elasticsearch Helper Index management][elasticsearch_helper_index_management]
  module.

## Usage

1. Go to the `/admin/config/search/elasticsearch_helper/index` to manage index plugins.
2. There are multiple operations available for index plugins:
   - Setup the indices managed by index plugin.
   - Queue content items for re-indexing.
   - Drop the indices managed by index plugin.

[elasticsearch_download]: https://www.elastic.co/downloads/elasticsearch
[elasticsearch_helper]: https://www.drupal.org/project/elasticsearch_helper
[elasticsearch_helper_index_management]: https://www.drupal.org/project/elasticsearch_helper_index_management
[elasticsearch_client]: https://github.com/elastic/elasticsearch-php
