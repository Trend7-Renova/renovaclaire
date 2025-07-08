<?php
add_filter('timber/context', function ($context) {
    $path = get_stylesheet_directory() . '/partenaires/';
    $images = glob($path.'/*.*');

    $images = array_map('abs_path_to_url', $images);
    $context['partenaires'] = $images;

    return $context;
});
