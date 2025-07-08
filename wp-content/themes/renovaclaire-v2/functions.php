<?php
/**
 * Timber starter-theme
 * https://github.com/timber/starter-theme
 */

// Load Composer dependencies.
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/src/StarterSite.php';

Timber\Timber::init();

// Sets the directories (inside your theme) to find .twig files.
Timber::$dirname = [ 'templates', 'views' ];

foreach(glob(__DIR__.'/inc/*.inc.php') as $php) {
    include $php;
}
foreach(glob(__DIR__.'/php/*.php') as $php) {
    include $php;
}

new StarterSite();




if($_GET['trash-all']??false) {

add_action('init', function () {
    $args = [
        'post_type'      => 'post',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ];

    $posts = get_posts($args);

    foreach ($posts as $post_id) {
        wp_trash_post($post_id);
    }

echo count($posts);
exit;
});

}
