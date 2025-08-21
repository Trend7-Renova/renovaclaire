<?php
/**
 * Plugin Name: Allow SVG Uploads & Preview
 * Description: Permet l’upload de fichiers SVG dans la médiathèque WordPress, les affiche correctement dans l’admin, et évite toute tentative de redimensionnement.
 * Version:     1.0.0
 * Author:      ChatGPT
 */

// 1) Autoriser l’upload des SVG (MIME type)
add_filter('upload_mimes', function ($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});

// 2) Corriger la détection du type de fichier pour les SVG (WP 5.1+)
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
    $filetype = wp_check_filetype($filename, $mimes);

    if (isset($filetype['ext']) && $filetype['ext'] === 'svg') {
        $data['ext']  = 'svg';
        $data['type'] = 'image/svg+xml';
    }

    return $data;
}, 10, 4);

// 3) (Optionnel) Sanitize simple des SVG à l’upload (supprime <script> & attributs on*)
add_filter('wp_handle_upload_prefilter', function ($file) {
    if (! isset($file['type']) || $file['type'] !== 'image/svg+xml') {
        return $file;
    }

    $path = $file['tmp_name'];
    $svg  = @file_get_contents($path);
    if ($svg === false) {
        return $file;
    }

    $svg = sanitize_svg_minimal($svg);
    @file_put_contents($path, $svg);

    return $file;
});

// 4) Empêcher toute logique de “big image” / redimensionnement pour les SVG
add_filter('big_image_size_threshold', function ($threshold, $imagesize, $file, $attachment_id) {
    $mime = 'application/octet-stream';
    if ($attachment_id) {
        $mime = get_post_mime_type($attachment_id);
    } elseif (! empty($file)) {
        $type = wp_check_filetype($file);
        if (! empty($type['type'])) {
            $mime = $type['type'];
        }
    }

    if ($mime === 'image/svg+xml') {
        return false;
    }

    return $threshold;
}, 10, 4);

// 5) Considérer les SVG comme “affichables” dans la grille Media
add_filter('file_is_displayable_image', function ($result, $path) {
    if (is_string($path) && preg_match('/\.svg$/i', $path)) {
        return true;
    }
    return $result;
}, 10, 2);

// 6) Préparer des données d’attachement utiles pour l’UI Media (aperçu + taille “full”)
add_filter('wp_prepare_attachment_for_js', function ($response, $attachment, $meta) {
    if ($response['type'] === 'image' && $response['subtype'] === 'svg+xml') {
        // Toujours servir l’URL brute du SVG pour les tailles
        $url = isset($response['url']) ? $response['url'] : wp_get_attachment_url($attachment->ID);

        $response['sizes'] = array(
            'full' => array(
                'url'         => $url,
                'width'       => null,
                'height'      => null,
                'orientation' => 'portrait',
            ),
        );

        // Marquer comme non recadrable dans l’éditeur d’images
        $response['editing'] = false;
        $response['can']['edit_image'] = false;
    }
    return $response;
}, 10, 3);

// 7) Désactiver les outils d’édition d’image pour SVG (évite erreurs)
add_filter('image_edit_supports', function ($supports, $mime) {
    if ($mime === 'image/svg+xml') {
        return false;
    }
    return $supports;
}, 10, 2);

// 8) Un peu de CSS pour de jolis aperçus SVG dans la médiathèque (admin)
add_action('admin_head', function () {
    echo '<style>
    .attachment .thumbnail img[src$=".svg"],
    .media-modal .attachment-media-view .thumbnail img[src$=".svg"]{
        width: 100% !important;
        height: auto !important;
        background: #fff;
        border: 1px solid #e2e2e2;
        box-sizing: border-box;
    }
    .attachment .type-image.subtype-svg+xml .thumbnail{
        display: block;
        overflow: hidden;
    }
    </style>';
});

/**
 * Nettoyage minimal d’un contenu SVG.
 *
 * Supprime les balises <script> et les attributs inline commençant par "on" (ex: onclick).
 * Ce n’est PAS un sanitizer exhaustif, mais une étape prudente avant stockage.
 *
 * @param string $svg Contenu SVG brut.
 * @return string SVG nettoyé.
 */
function sanitize_svg_minimal($svg) {
    // Supprimer les <script>...</script>
    $svg = preg_replace('#<\s*script[^>]*>.*?<\s*/\s*script\s*>#is', '', $svg);
    // Supprimer les attributs on* (onclick, onload, etc.)
    $svg = preg_replace('/\son\w+\s*=\s*("|\').*?\1/ims', '', $svg);
    // Supprimer les xmlns:xlink redondants mal formés (optionnel)
    $svg = preg_replace('/\s+xmlns:xlink=("|\')(.*?)\1/i', ' xmlns:xlink="http://www.w3.org/1999/xlink"', $svg);
    return $svg;
}
