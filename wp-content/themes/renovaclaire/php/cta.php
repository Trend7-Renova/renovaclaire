<?php

add_filter('timber/twig', function ($twig) {
    // Adding a custom filter
    $twig->addFilter(new \Twig\TwigFilter('cta', function ($cta) {

        if($cta['type_de_cible'] == 'page') {
            return $cta['cible'];
        } else {
            return $cta['uri'];

        }
    }));

    return $twig;
});
