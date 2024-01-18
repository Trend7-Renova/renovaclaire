<?php
add_filter('timber/context', function ($context) {
    // Assuming your CSS file is in the root of your theme directory
    $css_file_path = get_stylesheet_directory() . '/style-min.css';

    // Using file modification time as version number
    $context['css_version'] = file_exists($css_file_path) ? date('Y-m-d-H-i-s',filemtime($css_file_path)) : '1.0.0';

    return $context;
});
