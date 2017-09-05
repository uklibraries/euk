<?php 
$templates_dir = 'handlebars';

$title = 'ExploreStatic';
$site_title = 'ExploreUK';
$search_placeholder = 'ExploreUK';

$solr = 'https://exploreuk.uky.edu/solr/select/';

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

$id_field = 'id';
$text_field = 'text_s';

$hl = true;
$hl_fl = 'title_display';
$hl_simple_pre = '<em>';
$hl_simple_post = '</em>';
$hl_snippets = 3;

function type_for($format, $type) {
    $type_for = array(
        'archival_material' => 'collection',
        'athletic publications' => 'text',
        'books' => 'text',
        'collections' => 'collection',
        'course catalogs' => 'text',
        'directories' => 'text',
        'images' => 'image',
        'journals' => 'text',
        'ledgers' => 'text',
        'maps' => 'image',
        'minutes' => 'text',
        'newspapers' => 'text',
        'oral histories' => 'sound',
        'scrapbooks' => array('text', 'image'),
        'theses' => 'text',
        'yearbooks' => array('text', 'image'),
    );
    if (array_key_exists($format, $type_for)) {
        return $type_for[$format];
    }
    else {
        return $type;
    }
}
