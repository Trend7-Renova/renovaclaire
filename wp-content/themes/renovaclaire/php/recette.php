<?php
add_action('wp_head', function() {
    if (!is_admin() && strpos($_SERVER['HTTP_HOST'], 'recette') !== false) {
        echo '<meta name="robots" content="noindex, nofollow">' . "\n";
    }
});
