<?php
/**
 * Plugin Name: Désactivation totale des commentaires
 * Description: Coupe commentaires et pings partout : front, admin, REST, XML-RPC, flux.
 *              Mis en place après la purge du spam SEO (août 2026).
 */

defined( "ABSPATH" ) || exit;

// Ferme commentaires et pings sur tous les contenus, quel que soit le réglage en base.
add_filter( "comments_open", "__return_false", 20, 2 );
add_filter( "pings_open", "__return_false", 20, 2 );

// N affiche jamais de commentaire existant.
add_filter( "comments_array", "__return_empty_array", 20, 2 );
add_filter( "get_comments_number", "__return_zero", 20, 2 );

// Bloque toute soumission directe sur wp-comments-post.php.
add_action( "pre_comment_on_post", function () {
	wp_die(
		esc_html__( "Les commentaires sont désactivés sur ce site.", "renovaclaire" ),
		"",
		array( "response" => 403 )
	);
} );

// Coupe les pingbacks / trackbacks XML-RPC.
add_filter( "xmlrpc_methods", function ( $methods ) {
	unset( $methods["pingback.ping"], $methods["pingback.extensions.getPingbacks"] );
	return $methods;
} );
add_filter( "pre_option_default_ping_status", function () { return "closed"; } );
add_filter( "pre_option_default_comment_status", function () { return "closed"; } );
add_filter( "wp_headers", function ( $headers ) {
	unset( $headers["X-Pingback"] );
	return $headers;
} );

// Retire les routes REST des commentaires.
add_filter( "rest_endpoints", function ( $endpoints ) {
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( 0 === strpos( $route, "/wp/v2/comments" ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
} );

// Supprime le support des commentaires sur tous les types de contenu.
add_action( "init", function () {
	foreach ( get_post_types() as $type ) {
		if ( post_type_supports( $type, "comments" ) ) {
			remove_post_type_support( $type, "comments" );
			remove_post_type_support( $type, "trackbacks" );
		}
	}
}, 100 );

// Retire les flux de commentaires.
add_action( "template_redirect", function () {
	if ( is_comment_feed() ) {
		wp_die( "", "", array( "response" => 404 ) );
	}
}, 1 );
remove_action( "wp_head", "feed_links_extra", 3 );

// Nettoie l interface d administration.
add_action( "admin_menu", function () {
	remove_menu_page( "edit-comments.php" );
	remove_meta_box( "commentsdiv", "", "normal" );
	remove_meta_box( "commentstatusdiv", "", "normal" );
	remove_meta_box( "dashboard_recent_comments", "dashboard", "normal" );
}, 100 );

add_action( "admin_init", function () {
	global $pagenow;
	if ( "edit-comments.php" === $pagenow || "options-discussion.php" === $pagenow ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
} );

add_action( "wp_before_admin_bar_render", function () {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( "comments" );
} );
