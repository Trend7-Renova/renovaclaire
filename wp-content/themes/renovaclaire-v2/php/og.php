<?php

add_filter('timber/twig', function ($twig) {
    // Adding a custom filter
    $twig->addFilter(new \Twig\TwigFilter('og_image', function ($post) {
        if(is_home()) return;
        $image = get_field('og_image', $post->ID);
        $flouter = get_field('og_flouter', $post->ID);

        if (!$image) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
        }
        $image = $image['sizes']['large'] ?? $image[0] ?? false;
        if ($image) {
            if (!$flouter) {
                return $image;
            }

            return site_url('/og/' . $post->ID . '.jpg');
        }
    }));
    $twig->addFilter(new \Twig\TwigFilter('og_titre', function ($post) {

        $titre = get_field('og_titre', $post->ID);

        if (!$titre) {
            $titre = $post->post_title;
        }

        return $titre;

    }));
    $twig->addFilter(new \Twig\TwigFilter('og_description', function ($post) {

        $description = get_field('og_description', $post->ID);

        if (!$description) {
            $description = get_field('description', $post->ID);
        }
        if (!$description) {
            $description = html_entity_decode(get_the_excerpt($post->ID));
        }
        return $description;

    }));

    return $twig;
});
