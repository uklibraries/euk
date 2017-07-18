<?php
require_once('init.php');
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}
else {
    exit;
}

$doc = get_document($id);

if (array_key_exists($text_field, $doc)) {
    print '<pre>' . implode("\n", $doc[$text_field]) . "</pre>\n";
}
