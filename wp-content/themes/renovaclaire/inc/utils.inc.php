<?php

function abs_path_to_url($path = '')
{
    $url = str_replace(
        wp_normalize_path(untrailingslashit(ABSPATH)),
        site_url(),
        wp_normalize_path($path)
    );
    return esc_url_raw($url);
}

/**
 * Retrieve the contents of a file, with caching using WordPress transients.
 *
 * @param string $filename The name of the file to retrieve the contents from.
 * @param int $expiration The time until expiration in seconds. Default is 3600 seconds (1 hour).
 * @return string|false The content of the file, or false on failure.
 */
function cache_file_get_contents($filename, $expiration = 3600)
{
    // Generate a unique key for the transient based on the filename.
    $transient_key = 'file_contents_' . md5($filename);

    // Try to get the cached content.
    $cached_content = get_transient($transient_key);

    if ($cached_content !== false) {
        // Return the cached content if available.
        return $cached_content;
    }

    // Fetch the content from the file.
    $content = file_get_contents($filename);

    if ($content !== false) {
        // Cache the content if successfully retrieved.
        set_transient($transient_key, $content, $expiration);
    }

    return $content;
}

function urlToPathContent($url)
{
    if(!$url) return;
    if(!strstr($url, 'wp-content')) return;
    return ABSPATH . 'wp-content/' . explode('/wp-content/', $url)[1];
}
