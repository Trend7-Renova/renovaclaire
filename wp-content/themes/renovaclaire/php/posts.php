<?php

add_filter('timber/context', function ($context) {
    $args = [
        'post_type' => 'post',
        'posts_per_page' => 3, // Number of posts to display
    ];
    $context['posts_last'] = Timber::get_posts( $args );
    $args = [
        'post_type' => 'post',
        'posts_per_page' => 100, // Number of posts to display
    ];
    $context['post_archive'] = Timber::get_posts( $args );
    return $context;
});
