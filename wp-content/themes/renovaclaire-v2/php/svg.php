<?php
add_filter('timber/twig', function ($twig) {
    // Adding a function to Twig named 'svg'
    $twig->addFunction(new \Twig\TwigFunction('svg', function ($slug) {

        if (strstr($slug, 'https')) {
            $content = cache_file_get_contents($slug);
            $slug = explode('.',basename($slug))[0];
        } else {
            $content = '';
            $path = get_stylesheet_directory() . '/svg/' . $slug . '.svg';
            if (file_exists($path) && pathinfo($path, PATHINFO_EXTENSION) === 'svg') {
                $content = file_get_contents($path);
            }
        }

        if($content && $slug) {
            $content = str_replace('<svg ', '<svg data-slug="' . $slug . '" ', $content);
        }
        return $content;
    }));
    return $twig;
});
