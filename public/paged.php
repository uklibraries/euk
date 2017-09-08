<?php
require_once('init.php');
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}
else {
    $id = 'unknown';
}

$data = array(
    'title' => $title,
    'site_title' => $site_title,
    'search_placeholder' => $search_placeholder,
);
$data['back_to_search'] = link_to_query($query);

$doc = get_document($id);
$format = $doc['format'];
$flat = array();
foreach ($doc as $key => $value) {
    if (is_array($value) and count($value) > 0) {
        $flat[$key] = $value[0];
    }
    elseif (isset($value)) {
        $flat[$key] = $value;
    }
    else {
        $flat[$key] = '';
    }
}
$metadata = array();
$desired = array(
    array('Title', 'title_display'),
    array('Creator', 'author_display'),
    array('Format', 'format'),
    array('Publication date', 'pub_date'),
    array('Date uploaded', 'date_digitized_display'),
    array('Language', 'language_display'),
    array('Publisher', 'publisher_display'),
    array('Type', 'type_display'),
    array('Accession number', 'accession_number_display'),
    array('Source', 'source_s'),
    array('Coverage', 'coverage_s'),
    array('Finding aid', 'finding_aid_url_s'),
    array('Metadata record', 'mets_url_display'),
    array('Rights', 'usage_display'),
);
foreach ($desired as $row) {
    $label = $row[0];
    $key = $row[1];
    $link = false;
    if ($key === 'type_display') {
        $value = type_for($doc['format'], $doc['type_display']);
    }
    else {
        if (is_array($doc[$key])) {
            $value = implode('.  ', $doc[$key]);
        }
        elseif (isset($doc[$key])) {
            $value = $doc[$key];
        }
        else {
            $value = false;
        }
    }
    if ($key === 'finding_aid_url_s' or $key === 'mets_url_display') {
        $link = true;
    }
    if ($value) {
        $metadata[] = array(
            'label' => $label,
            'key' => $key,
            'value' => $value,
            'link' => $link,
        );
    }
}

if (array_key_exists('finding_aid_url_s', $doc)) {
    $entry = array(
        'label' => 'Collection guide',
        'anchor' => true,
        'key' => 'collection_guide',
        'value' => '/catalog/' . $doc['object_id_s'][0] . link_to_query($query),
        'link' => true,
    );
    $metadata[] = $entry;
}

if (array_key_exists('pdf_url_display', $doc)) {
    $entry = array(
        'label' => 'PDF',
        'anchor' => true,
        'key' => 'pdf_url_display',
        'value' => $doc['pdf_url_display'][0],
        'link' => true,
    );
    $metadata[] = $entry;
}

if (array_key_exists('reference_image_url_s', $doc)) {
    $entry = array(
        'label' => 'Reference image',
        'anchor' => true,
        'key' => 'reference_image_url_s',
        'value' => $doc['reference_image_url_s'][0],
        'link' => true,
    );
    $metadata[] = $entry;
}

$flat['metadata'] = $metadata;

$pages = get_pages($id);
if ($pages) {
    $data['script'] = array(
        'json' => json_encode($pages),
    );
}
print $templates['books-embed']($data);
