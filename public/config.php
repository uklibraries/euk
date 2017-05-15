<?php 
$templates_dir = 'handlebars';

$solr = 'https://selene.ukpdp.org/solr/select/';

$facets = array(
    'format',
    'source_s',
    'pub_date',
);

$facets_titles = array(
    'format' => 'format',
    'source_s' => 'collection',
    'pub_date' => 'publication year',
);

$hit_fields = array(
    'title' => 'title_display',
    'thumb' => 'thumbnail_url_s',
    'source' => 'source_s',
    'pubdate' => 'pub_date',
    'format' => 'format',
);

$hits_per_page = 20;
$id_field = 'id';

$hl = true;
$hl_fl = 'title_display';
$hl_simple_pre = '<em>';
$hl_simple_post = '</em>';
$hl_snippets = 3;
