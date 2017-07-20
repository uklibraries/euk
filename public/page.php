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

$flat['metadata'] = $metadata;

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
    $flat['embed_url'] = '/catalog/' . $id . '/paged';
    if (array_key_exists($text_field, $doc)) {
        $flat['text'] = array(
            'href' => '/catalog/' . $id . '/text',
        );
    }
    $data['item'] = $templates['books']($flat);
    print $templates['show']($data);
    break;
case 'collections':
    /* We'll want to embed this eventually */
    $target = "https://nyx.uky.edu/fa/findingaid/?id=$id";
    header('Location: '. $target);
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
