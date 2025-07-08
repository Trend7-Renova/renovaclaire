<?php

add_action('wp_enqueue_scripts', function () {
    $json_path = get_template_directory() . '/dist/rev-css.json'; // Adjust the path to your rev.json
    $rev = json_decode(file_get_contents($json_path), true);
    if (isset($rev['style.css'])) {
        wp_enqueue_style('style.css', get_template_directory_uri() . '/dist/' . $rev['style.css']);
    }

    $json_path = get_template_directory() . '/dist/rev-js.json'; // Adjust the path to your rev.json
    $rev = json_decode(file_get_contents($json_path), true);
    if (isset($rev['scripts.js'])) {
        wp_enqueue_script('scripts.js', get_template_directory_uri() . '/dist/scripts.js?' . $rev['scripts.js']);
        // wp_enqueue_script('scripts.js', get_template_directory_uri() . '/dist/' . $rev['scripts.js']);
    }
});
