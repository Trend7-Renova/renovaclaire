<?php
add_filter('timber/twig', function ($twig) {
    // Adding a function to Twig named 'svg'
    $twig->addFunction(new \Twig\TwigFunction('svg', function ($slug) {

        $content = '';
        $path = get_stylesheet_directory() . '/svg/' . $slug . '.svg';
        if (file_exists($path) && pathinfo($path, PATHINFO_EXTENSION) === 'svg') {
            $content = file_get_contents($path);
            $content = str_replace('<svg ', '<svg data-slug="' . $slug . '" ', $content);
        }
        return $content;
    }));
    return $twig;
});
