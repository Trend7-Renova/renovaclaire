<?php

/**
 * Récupère la date de modification d'un article par son ID
 *
 * @param int $post_id ID de l'article.
 * @return string|null Date de modification de l'article, ou null si non trouvé.
 */
function get_post_modified_date_by_id( $post_id ) {
    $post = get_post( $post_id );
    return $post ? $post->post_modified : null;
}
