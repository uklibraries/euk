<?php
require_once('init.php');
initialize_query();

$result = get_search_results();

$data = array(
    'site_title' => $site_title,
);

# Search
$data['query'] = htmlspecialchars(json_encode($query));
$data['q'] = $query['q'];
$data['search_link'] = "$solr?" . build_search_params();
$data['back_to_search'] = link_to_query($query);

# Facets
$data['active_facets'] = array();
foreach ($query['f'] as $f_term => $value) {
    $remove_link = remove_filter($f_term, $value);
    $field_label = facet_displayname($f_term);
    $facet_counts = $result['facet_counts']['facet_fields'][$f_term];
    $count = 0;
    if (count($facet_counts) > 0) {
        $navs_sensible = makeNavsSensible($facet_counts);
        $count = $navs_sensible[$value];
    }
    $data['active_facets'][] = array(
        'field_label' => $field_label,
        'remove_link' => $remove_link,
        'field_raw' => $f_term,
        'value_label' => $value,
        'count' => $count,
    );
}

$data['facets'] = array();
foreach ($facets as $facet) {
    $facet_counts = $result['facet_counts']['facet_fields'][$facet];
    if (count($facet_counts) > 2) {
        $navs_sensible = makeNavsSensible($facet_counts);
        $values = array();
        foreach ($navs_sensible as $label => $count) {
            $add_link = add_filter($facet, $label);
            $values[] = array(
                'add_link' => $add_link,
                'value_label' => $label,
                'count' => $count,
            );
        }
        $data['facets'][] = array(
            'field_label' => facet_displayname($facet),
            'values' => $values,
        );
    }
}

# Pagination and results
if (!on_front_page()) {
    $data['on_front_page'] = false;
    $data['pagination'] = array();
    $data['results'] = array();
    if (intval($result['response']['numFound']) > 0) {
        #pagination
        $pagination_data = array(
            'first' => $query['offset'] + 1,
            'last' => $query['offset'] + $query['rows'],
            'count' => $result['response']['numFound'],
        );
        if ($query['offset'] > 0) {
            $pagination_data['previous'] = previous_link();
        }
        if ($pagination_data['last'] <= $pagination_data['count']) {
            $pagination_data['next'] = next_link();
        }
        else {
            $pagination_data['last'] = $pagination_data['count'];
        }
        $data['pagination'] = $pagination_data;

        # results
        $docs = $result['response']['docs'];
        $results = array();
        for ($i = 0; $i < count($docs); $i++) {
            $results_data = array();
            # raw to begin
            foreach ($hit_fields as $field => $solr_field) {
                $raw_field = null;
                if (isset($docs[$i][$solr_field])) {
                    $raw_field = $docs[$i][$solr_field];
                    if (is_array($raw_field)) {
                        $results_data[$field] = $raw_field[0];
                    }
                    else {
                        $results_data[$field] = $raw_field;
                    }
                }
            }
            # cleanup
            if (isset($results_data['thumb'])) {
                $results_data['thumb'] = str_replace('http:', 'https:', $results_data['thumb']);
                $results_data['thumb'] = str_replace('_tb.jpg', '_ftb.jpg', $results_data['thumb']);
            }
            $results_data['link'] = '/catalog/' . $docs[$i]['id'] . link_to_query($query);
            $results_data['number'] = $query['offset'] + $i + 1;
            if ($results_data['format'] === 'collections') {
                $results_data['target'] = ' target="_blank"';
            }
            $results[] = $results_data;
        }
        $data['results'] = $results;
    }
}
else {
    $data['on_front_page'] = true;
    #$random = str_replace('http:', 'https:', file_get_contents('https://exploreuk.uky.edu/catalog/random'));
    #$data['results'] = $templates['home'](array('random' => $random));
}

# JSON
$data['json'] = htmlspecialchars(json_encode($data));

print $templates['index']($data);
