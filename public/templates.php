<?php 
require_once('vendor/autoload.php');
require_once('config.php');

use LightnCandy\LightnCandy;

# Load precompiled templates
$templates = array();
foreach (glob("$templates_dir/*.php") as $template) {
    $handle = basename($template, '.php');
    $templates[$handle] = include($template);
}

function facet_displayname($facet) {
    global $facets_titles;
    if (isset($facets_titles[$facet])) {
        return $facets_titles[$facet];
    }
    else {
        return 'unknown';
    }
}

function makeNavsSensible($navs) {
    $newNav = array();
    for ($i =0; $i < count($navs); $i += 2) {
        $newNav[$navs[$i]] = $navs[$i + 1];
    }
    return $newNav;
}
