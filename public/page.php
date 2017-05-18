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

switch ($format) {
case 'images':
    $data['item'] = $templates['item-image']($flat);
    $data['scripts'] = $templates['script-image'](array_merge(
        $flat,
        array(
            'osd_id' => 'viewer',
            'prefix_url' => '/openseadragon/images/',
            'ref_id' => 'reference_image',
        )
    ));
    print $templates['show']($data);
    break;
default:
    $pieces = array();
    foreach ($doc as $field => $value) {
        if (is_array($value)) {
            $value = implode('; ', $value);
        }
        $pieces[] = "<b>$field</b>: $value";
    }
    $data['item'] = '<ul><li>' . implode('</li><li>', $pieces) . "</li></ul>\n";
    $url = "$solr?" . document_query($id);
    $data['item'] .= "<p><a href=\"$url\">$url</a></p>";
    print $templates['show']($data);
    break;
}
