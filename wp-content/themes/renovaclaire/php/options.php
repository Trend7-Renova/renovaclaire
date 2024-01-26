<?php

register_nav_menus([
    'menu-principal' => 'Menu principal',
    'liens-utiles' => 'Liens utiles',
]);

add_filter('timber/context', function ($context) {

    $context['menu_principal'] = Timber::get_menu('menu-principal');
    $context['liens_utiles'] = Timber::get_menu('liens-utiles');

    $context['avis'] = get_field('avis', 'option');
    $context['home'] = get_field('home', 'option');
    $context['menu'] = get_field('menu', 'option');
    return $context;
});

add_action('admin_menu', function () {
    remove_menu_page('options'); // Remove the original menu
    add_menu_page('Options', 'Options', 'manage_options', 'options', '', '', 1); // Add new menu at the top
});
