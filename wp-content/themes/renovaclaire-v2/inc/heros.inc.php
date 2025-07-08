<?php


function get_hero($post)
{
    if (!get_field('afficher_hero', $post)) return;

    $hero = get_field('hero', $post);

    $hero['titre'] = $hero['titre'] ?: $post->post_title;
    $hero['texte'] = $hero['texte'] ?: get_field('description', $post) ?: $post->post_excerpt;
    return $hero;
}
