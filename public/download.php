<?php
require_once('init.php');
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}
else {
    $id = 'unknown';
}

if (isset($_GET['type'])) {
    switch ($_GET['type']) {
    case 'jpeg':
        $field = 'reference_image_url_s';
        $mime = 'image/jpeg';
        break;
    case 'pdf':
        $field = 'pdf_url_display';
        $mime = 'application/pdf';
        break;
    default:
        exit;
    }
}
else {
    exit;
}

$doc = get_document($id);
$url = $doc[$field];
if (is_array($url)) {
    $url = $url[0];
}

/* TODO: maybe have a metadata-determined filename? */
$name = basename($url);

header("Content-type: $mime");
header("Content-Disposition: attachment; filename=\"$name\"");

/* TODO: maybe stream this instead */
readfile($url);
