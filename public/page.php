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
case 'audio':
    $data['item'] = $templates['item-media'](array_merge(
        $flat,
        array(
            'audio' => array(
                'href_id' => "audio_$id",
                'href' => $flat['reference_audio_url_s'],
            ),
        )
    ));
    $data['scripts'] = $templates['script-media'](array());
    print $templates['show']($data);
    break;
case 'drawings (visual works)':
    /* fall through */
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
case 'annual reports':
    /* fall through */
case 'architectural drawings (visual works)':
    /* fall through */
case 'archival material':
    /* fall through */
case 'athletic publications':
    /* fall through */
case 'booklets':
    /* fall through */
case 'books':
    /* fall through */
case 'course catalogs':
    /* fall through */
case 'directories':
    /* fall through */
case 'indexes (reference sources)':
    /* fall through */
case 'journals':
    /* fall through */
case 'ledgers':
    /* fall through */
case 'maps':
    /* fall through */
case 'minutes':
    /* fall through */
case 'newsletters':
    /* fall through */
case 'newspapers':
    /* fall through */
case 'yearbooks':
    $pages = get_pages($id);
    if ($pages) {
        $data['scripts'] = $templates['script-book'](array(
            'json' => json_encode($pages),
        ));
    }
    #$data['item'] = json_encode($pages);
    print $templates['books']($data);
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
