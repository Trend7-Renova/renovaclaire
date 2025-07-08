<?php
add_action('init', function () {

    add_action('save_post', function ($pid, $post, $update) {
        $destination = ABSPATH . '/og/gen/' . $pid . '.jpg';
        @unlink($destination);
    }, 10, 3);
});
