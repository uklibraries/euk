<?php
require_once('config.php');

function on_front_page() {
    global $query;
    return !(isset($query['q']) or count($query['fq']) > 0 or $query['offset'] > 0);
}

function initialize_query() {
    global $query;
    $query = array(
        'q' => null,
        'fq' => array(),
        'offset' => 0,
    );
    $raw_params = array();
    if (isset($_SERVER['QUERY_STRING'])) {
        $raw_params = explode('&', str_replace('?', '', $_SERVER['QUERY_STRING']));
    }
    foreach ($raw_params as $raw_param) {
        preg_match('/(?<key>[^=]+)=(?<value>.*)/', $raw_param, $matches);
        $key = urldecode($matches['key']);
        $value = urldecode($matches['value']);
        if ($key == 'q' and strlen($value) > 0) {
            $query['q'] = $value;
        }
        elseif ($key == 'fq[]') {
            $query['fq'][] = $value;
        }
        elseif ($key == 'offset') {
            $query['offset'] = intval($value);
        }
    }
    return $query;
}

function link_to_query($query) {
    $pieces = array();
    if (strlen($query['q']) > 0) {
        $pieces[] = 'q=' . urlencode($query['q']);
    }
    foreach ($query['fq'] as $fq_term) {
        $pieces[] = 'fq[]=' . urlencode($fq_term);
    }
    if ($query['offset'] > 0) {
        $pieces[] = 'offset=' . urlencode($query['offset']);
    }
    return '?' . implode('&', $pieces);
}

function previous_link() {
    global $hits_per_page;
    global $query;
    $offset = $query['offset'] - $hits_per_page;
    if ($offset > 0) {
        $previous_query['offset'] = $offset;
    }
    else {
        $offset = 0;
    }
    return link_to_query(array(
        'q' => $query['q'],
        'fq' => $query['fq'],
        'offset' => $offset,
    ));
}

function next_link() {
    global $hits_per_page;
    global $query;
    $offset = $query['offset'] + $hits_per_page;
    if ($offset > 0) {
        $previous_query['offset'] = $offset;
    }
    else {
        $offset = 0;
    }
    return link_to_query(array(
        'q' => $query['q'],
        'fq' => $query['fq'],
        'offset' => $offset,
    ));
}

function add_filter($facet, $label) {
    global $query;
    $fq = $query['fq'];
    $fq[] = $facet . ':"' . $label . '"';
    return link_to_query(array(
        'q' => $query['q'],
        'fq' => $fq,
        'offset' => $query['offset'],
    ));
}

function remove_filter($facet, $label) {
    global $query;
    $sought_term = $facet . ':"' . $label . '"';
    $fq = array();
    foreach ($query['fq'] as $fq_term) {
        if ($fq_term != $sought_term) {
            $fq[] = $fq_term;
        }
    }
    return link_to_query(array(
        'q' => $query['q'],
        'fq' => $fq,
        'offset' => $query['offset'],
    ));
}

function build_search_params() {
    global $query;
    global $facets;
    global $hits_per_page;
    $q = $query['q'];
    $fq = $query['fq'];
    $offset = $query['offset'];
    $pieces = array();
    $pieces[] = "rows=$hits_per_page";
    $pieces[] = 'wt=json';
    $pieces[] = 'q=' . urlencode($q);
    if ($offset > 0) {
        $pieces[] = "start=$offset";
    }
    if (count($facets) > 0) {
        $pieces[] = 'facet=true';
        $pieces[] = 'facet.mincount=1';
        $pieces[] = 'facet.limit=20';
        foreach ($facets as $facet) {
            $pieces[] = "facet.field=$facet";
        }
    }
    if (count($fq) > 0) {
        foreach ($fq as $spec) {
            $pieces[] = 'fq=' . urlencode($spec);
        }
    }
    # compound object
    $pieces[] = 'fq=' . urlencode("compound_object_split_b:true");
    return implode('&', $pieces);
}
