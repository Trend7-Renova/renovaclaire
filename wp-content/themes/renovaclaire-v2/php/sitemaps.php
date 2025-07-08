<?php
add_filter('wp_sitemaps_add_provider', function($provider, $name) {
    if (in_array($name, ['users', 'taxonomies'])) {
        return false;
    }
    return $provider;
}, 10, 2);
