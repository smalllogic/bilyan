<?php
defined('ABSPATH') || exit;
add_action('init', 'bilyan_register_types');
add_action('bilyan_category_add_form_fields', function () { ?>
    <div class="form-field"><label for="bilyan_category_image">Category image URL</label><input type="url" name="bilyan_category_image" id="bilyan_category_image" placeholder="https://example.com/category.jpg"><p>Optional. If empty, Bilyan uses a bundled illustration based on the category name.</p></div>
<?php });
add_action('bilyan_category_edit_form_fields', function ($term) { ?><tr class="form-field"><th><label for="bilyan_category_image">Category image URL</label></th><td><input type="url" name="bilyan_category_image" id="bilyan_category_image" value="<?php echo esc_attr(get_term_meta($term->term_id, '_bilyan_category_image', true)); ?>" class="regular-text"><p class="description">Optional. If empty, Bilyan uses a bundled illustration based on the category name.</p></td></tr><?php });
function bilyan_save_category_image($term_id) {
    if (!current_user_can('manage_categories')) return;
    if (isset($_POST['bilyan_category_image']) && is_string($_POST['bilyan_category_image'])) {
        $url = esc_url_raw(wp_unslash($_POST['bilyan_category_image']));
        $url ? update_term_meta($term_id, '_bilyan_category_image', $url) : delete_term_meta($term_id, '_bilyan_category_image');
    }
}
add_action('created_bilyan_category', 'bilyan_save_category_image');
add_action('edited_bilyan_category', 'bilyan_save_category_image');
function bilyan_register_types() {
    register_post_type('bilyan_product', [
        'labels' => ['name' => 'Products', 'singular_name' => 'Product', 'add_new_item' => 'Add product', 'edit_item' => 'Edit product'],
        'public' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-products',
        'has_archive' => 'shop', 'rewrite' => ['slug' => 'product'],
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'], 'taxonomies' => ['bilyan_category'],
    ]);
    register_taxonomy('bilyan_category', 'bilyan_product', [
        'labels' => ['name' => 'Product categories', 'singular_name' => 'Product category'],
        'public' => true, 'hierarchical' => true, 'show_in_rest' => true,
        'rewrite' => ['slug' => 'product-category'], 'show_admin_column' => true,
    ]);
    register_post_type('bilyan_faq', [
        'labels' => ['name' => 'FAQs', 'singular_name' => 'FAQ', 'add_new_item' => 'Add FAQ', 'edit_item' => 'Edit FAQ', 'all_items' => 'All FAQs'],
        'public' => false, 'publicly_queryable' => false, 'show_ui' => true, 'show_in_rest' => true,
        'menu_icon' => 'dashicons-editor-help', 'supports' => ['title', 'editor', 'page-attributes'],
        'menu_position' => 26, 'exclude_from_search' => true,
    ]);
    register_post_type('bilyan_order', [
        'labels' => ['name' => 'Orders', 'singular_name' => 'Order', 'edit_item' => 'Order details'],
        'public' => false, 'publicly_queryable' => false, 'show_ui' => true, 'show_in_rest' => false,
        'exclude_from_search' => true, 'menu_icon' => 'dashicons-clipboard', 'supports' => ['title'],
        'capabilities' => ['create_posts' => 'do_not_allow', 'edit_posts' => 'manage_options', 'edit_others_posts' => 'manage_options', 'publish_posts' => 'manage_options', 'read_private_posts' => 'manage_options', 'delete_posts' => 'manage_options'],
        'map_meta_cap' => true,
    ]);
}
add_action('add_meta_boxes', function () {
    add_meta_box('bilyan-product-fields', 'Product information', 'bilyan_product_fields', 'bilyan_product', 'normal', 'high');
    add_meta_box('bilyan-order-details', 'Customer & order details', 'bilyan_order_details', 'bilyan_order', 'normal', 'high');
});
function bilyan_product_fields($post) {
    wp_nonce_field('bilyan_product_save', 'bilyan_product_nonce');
    ?>
    <p>Use the <strong>title</strong> for the product name, <strong>Excerpt</strong> for the short description, the main editor for full details, and <strong>Featured image</strong> for the product photo.</p>
    <p><label>Price (<?php echo esc_html(bilyan_setting('currency', 'USD')); ?>, optional)<br><input name="bilyan_price" type="number" step="0.01" min="0" max="99999999" value="<?php echo esc_attr(get_post_meta($post->ID, '_bilyan_price', true)); ?>"></label> Leave empty to hide the price on product pages and cards.</p>
    <p><label>SKU / product code<br><input name="bilyan_sku" type="text" maxlength="80" value="<?php echo esc_attr(get_post_meta($post->ID, '_bilyan_sku', true)); ?>"></label></p>
    <p><label><input type="checkbox" name="bilyan_unavailable" value="1" <?php checked(get_post_meta($post->ID, '_bilyan_unavailable', true), '1'); ?>> Unavailable (disable ordering)</label></p>
    <p><label><input type="checkbox" name="bilyan_featured" value="1" <?php checked(get_post_meta($post->ID, '_bilyan_featured', true), '1'); ?>> Featured on the homepage</label></p>
    <?php
}
add_action('save_post_bilyan_product', function ($id) {
    if (!isset($_POST['bilyan_product_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bilyan_product_nonce'])), 'bilyan_product_save') || !current_user_can('edit_post', $id) || wp_is_post_autosave($id) || wp_is_post_revision($id)) return;
    $price = isset($_POST['bilyan_price']) && is_scalar($_POST['bilyan_price']) ? trim(wp_unslash($_POST['bilyan_price'])) : '';
    update_post_meta($id, '_bilyan_price', $price !== '' && is_numeric($price) && (float) $price >= 0 && (float) $price <= 99999999 ? number_format((float) $price, 2, '.', '') : '');
    update_post_meta($id, '_bilyan_sku', sanitize_text_field(wp_unslash($_POST['bilyan_sku'] ?? '')));
    foreach (['unavailable', 'featured'] as $field) update_post_meta($id, '_bilyan_' . $field, isset($_POST['bilyan_' . $field]) ? '1' : '0');
});
add_action('pre_get_posts', function ($query) {
    if (is_admin() || !$query->is_main_query()) return;
    if ($query->is_post_type_archive('bilyan_product') || $query->is_tax('bilyan_category') || ($query->is_search() && $query->get('post_type') === 'bilyan_product')) {
        $query->set('posts_per_page', 20);
        $query->set('has_password', false);
        $sort = isset($_GET['sort']) && is_string($_GET['sort']) ? sanitize_key($_GET['sort']) : '';
        if ($sort === 'name') { $query->set('orderby', 'title'); $query->set('order', 'ASC'); }
        elseif ($sort === 'oldest') { $query->set('orderby', 'date'); $query->set('order', 'ASC'); }
        else { $query->set('orderby', 'date'); $query->set('order', 'DESC'); }
    }
});
