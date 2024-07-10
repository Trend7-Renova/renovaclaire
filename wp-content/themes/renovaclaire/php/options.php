<?php

register_nav_menus([
    'menu-principal' => 'Menu principal',
    'liens-utiles' => 'Liens utiles',
]);

add_filter('timber/context', function ($context) {

    $context['liens_de_bas_de_page'] = Timber::get_menu(4);
    $context['menu_principal'] = Timber::get_menu(2);
    $context['liens_utiles'] = Timber::get_menu(3);

    $donnees = get_field('donnees', 'option');
    foreach ($donnees as &$ligne) {
        foreach ($ligne as &$item) {
            if ($item == '*') {
                continue;
            }

            $item = intval($item);
        }
    }
    $estimation = [];
    $estimation['donnees'] = $donnees;
    $estimation['categories'] = get_field('categories', 'option');
    $estimation['classes_energetiques'] = get_field('classes_energetiques', 'option');
    $estimation['classes_par_annees'] = get_field('classes_par_annees', 'option');
    $estimation['aides'] = get_field('aides', 'option');
    $context['estimation'] = $estimation;
    $context['avis'] = get_field('avis', 'option');
    $context['home'] = get_field('home', 'option');
    $context['menu'] = get_field('menu', 'option');
    $context['contact'] = get_field('contact', 'option');
    $context['identite'] = get_field('site', 'option');

    return $context;
});

// add_action('admin_menu', function () {
//     remove_menu_page('options'); // Remove the original menu
//     add_menu_page('Options', 'Options', 'manage_options', 'options', '', '', 1); // Add new menu at the top
// });
