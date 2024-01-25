<?php
add_filter('timber/context', function ($context) {

    $context['avis'] =get_field('avis', 'option');
    return $context;
});
