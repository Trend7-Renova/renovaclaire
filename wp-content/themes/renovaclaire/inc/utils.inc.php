<?php

function abs_path_to_url( $path = '' ) {
    $url = str_replace(
        wp_normalize_path( untrailingslashit( ABSPATH ) ),
        site_url(),
        wp_normalize_path( $path )
    );
    return esc_url_raw( $url );
}