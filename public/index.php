<?php
require_once('init.php');
initialize_query();

$data = array(
    'title' => $title,
    'site_title' => $site_title,
);

$url = "$solr?" . build_search_params();
$result = json_decode(file_get_contents($url), true);

if (!on_front_page()) {
    if (intval($result['response']['numFound']) > 0) {
        #pagination
        $pagination_data = array(
            'first' => $query['offset'] + 1,
            'last' => $query['offset'] + $hits_per_page,
            'count' => $result['response']['numFound'],
        );
        if ($query['offset'] > 0) {
            $pagination_data['previous'] = previous_link();
        }
        if ($pagination_data['last'] <= $pagination_data['count']) {
            $pagination_data['next'] = next_link();
        }
        $data['pagination'] = $templates['pagination']($pagination_data);

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
            }
            $results_data['link'] = "https://exploreuk.uky.edu/catalog/" . $docs[$i]['id'];
            $results_data['number'] = $query['offset'] + $i + 1;
            $results[] = $templates['hit-template']($results_data);
        }
        $data['results'] = implode('', $results);
    }
    else {
        $data['pagination'] = '';
        $data['results'] = $templates['no-results'](array());
    }
}
else {
    $random = str_replace('http:', 'https:', file_get_contents('https://exploreuk.uky.edu/catalog/random'));
    $data['results'] = $templates['home'](array('random' => $random));
}

# active filters
$filter_links = array();
if (count($query['fq']) > 0) {
    $navs = array();
    foreach ($query['fq'] as $fq_term) {
        preg_match('/(?<name>[^:]+):"(?<value>.*)"/', $fq_term, $matches);
        $name = $matches['name'];
        $value = $matches['value'];
        $link = remove_filter($name, $value);
        $data_filter = "$name:$value";
        $title = facet_displayname($name) . ':' . $value;
        $navs[] = "<a class=\"close\" href=\"$link\">&times;</a><a href=\"$link\" class=\"selectedNav\" data-filter=\"$data_filter\" title=\"$title\">$title</a><br>";
    }
    $data['active_filters'] = $templates['chosen-nav-template-php'](array(
        'navs' => implode('', $navs),
    ));
}
else {
}

# facets
$facet_links = array();
foreach ($facets as $facet) {
    $facet_counts = $result['facet_counts']['facet_fields'][$facet];
    if (count($facet_counts) > 0) {
        $navs_sensible = makeNavsSensible($facet_counts);
        $navs = array();
        foreach ($navs_sensible as $label => $count) {
            $link = add_filter($facet, $label);
            $navs[] = "<a href='$link' title='$label ($count)'>$label</a> ($count)<br>";
        }
        $facet_links[] = $templates['nav-template-php'](array(
            'title' => $facet,
            'navs' => implode('', $navs),
        ));
    }
}
$data['facets'] = implode('', $facet_links);

print $templates['index']($data);
