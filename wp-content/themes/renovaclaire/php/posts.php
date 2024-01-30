<?php

add_filter('timber/context', function ($context) {
    $args = [
        'post_type' => 'post',
        'posts_per_page' => 3, 
    ];
    $context['posts_last_3'] = Timber::get_posts( $args );
    $args = [
        'post_type' => 'post',
        'posts_per_page' => 4, 
    ];
    $context['posts_last_4'] = Timber::get_posts( $args );
    $args = [
        'post_type' => 'post',
        'posts_per_page' => 100, 
    ];
    $context['post_archive'] = Timber::get_posts( $args );
    return $context;
});
