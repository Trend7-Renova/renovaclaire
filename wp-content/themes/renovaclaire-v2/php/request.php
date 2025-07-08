<?php

add_filter('timber/context', function ($context) {

    $context['get'] = $_GET;
    return $context;
});

