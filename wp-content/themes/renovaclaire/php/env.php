<?php

add_action('admin_bar_menu', function($wp_admin_bar) {
    if (!is_user_logged_in()) return;

    $env = wp_get_environment_type();
    if ($env !== 'production') {
        $label = [
            'development' => '🛠 Dev',
            'staging'     => '🧪 Préprod',
            'local'       => '💻 Local'
        ][$env] ?? strtoupper($env);

        $wp_admin_bar->add_node([
            'id'    => 'environment-indicator',
            'title' => $label,
            'meta'  => ['class' => 'environment-indicator']
        ]);
    }
}, 100);

add_action('wp_head', function() {
    $env = wp_get_environment_type();
    $color = match($env) {
        'development' => '#0073aa',   // bleu WP (dev)
        'staging'     => '#f39c12',   // orange (préprod)
        'local'       => '#16a085',   // vert (local)
        default       => ''
    };
    if ($color) {
        echo "<style>
            #wpadminbar {
                background: {$color} !important;
            }
            #wpadminbar #wp-admin-bar-environment-indicator > .ab-item {
                background: rgba(0,0,0,0.2);
                color: white !important;
                font-weight: bold;
            }
        </style>";
    }
});
