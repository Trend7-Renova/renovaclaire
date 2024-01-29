<?php

add_action('wp_nav_menu_item_custom_fields', function ($item_id, $item, $depth, $args, $id) {
    $value = get_post_meta($item_id, 'mobile_only', true);
    ?>
    <p class="field-mobile-only description description-wide">
        <label for="edit-menu-item-mobile-only-<?php echo $item_id; ?>">
            <input type="checkbox" id="edit-menu-item-mobile-only-<?php echo $item_id; ?>" class="widefat code edit-menu-item-custom" name="menu-item-mobile-only[<?php echo $item_id; ?>]"<?php checked($value, 'yes'); ?> />
            Afficher seulement sur mobile
        </label>
    </p>
    <?php
}, 10, 5);

add_action('wp_update_nav_menu_item', function ($menu_id, $menu_item_db_id, $args) {
    if (isset($_POST['menu-item-mobile-only'][$menu_item_db_id])) {
        update_post_meta($menu_item_db_id, 'mobile_only', 'yes');
    } else {
        delete_post_meta($menu_item_db_id, 'mobile_only');
    }
}, 10, 3);
