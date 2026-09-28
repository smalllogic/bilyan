<?php
defined('ABSPATH') || exit;
define('BILYAN_VERSION', '1.5.1');
require_once get_template_directory() . '/inc/catalog.php';
require_once get_template_directory() . '/inc/orders.php';
require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/customize.php';

add_action('after_setup_theme', function () {
    load_theme_textdomain('bilyan', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', ['height' => 160, 'width' => 280, 'flex-height' => true, 'flex-width' => true]);
    register_nav_menus(['primary' => __('Primary navigation', 'bilyan')]);
});
add_filter('wp_nav_menu_items', function ($items, $args) {
    if (($args->theme_location ?? '') === 'primary' && stripos($items, 'faq') === false) {
        $items .= '<li class="menu-item menu-item-faq"><a href="' . esc_url(bilyan_page_url('faq')) . '">FAQ</a></li>';
    }
    return $items;
}, 10, 2);
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('bilyan', get_template_directory_uri() . '/assets/store.css', [], BILYAN_VERSION);
    if (is_front_page()) wp_enqueue_style('bilyan-home', get_template_directory_uri() . '/assets/home.css', ['bilyan'], BILYAN_VERSION);
    if (is_singular('bilyan_product')) wp_enqueue_style('bilyan-product', get_template_directory_uri() . '/assets/product.css', ['bilyan'], BILYAN_VERSION);
    wp_enqueue_script('bilyan', get_template_directory_uri() . '/assets/store.js', [], BILYAN_VERSION, true);
    wp_localize_script('bilyan', 'Bilyan', [
        'ajax' => admin_url('admin-ajax.php'), 'cartUrl' => bilyan_page_url('cart'),
        'storageKey' => 'bilyan-cart-' . get_current_blog_id() . '-' . md5(home_url()),
    ]);
});
function bilyan_page_url($slug) {
    $page = get_page_by_path($slug);
    return $page ? get_permalink($page) : home_url('/' . $slug . '/');
}
function bilyan_setting($key, $default = '') {
    $settings = get_option('bilyan_settings', []);
    return isset($settings[$key]) && $settings[$key] !== '' ? $settings[$key] : $default;
}
function bilyan_email() { return bilyan_setting('email', 'hellobilyan@gmail.com'); }
function bilyan_address() { return bilyan_setting('address', 'Somalia Mogadishu'); }
function bilyan_description() { return bilyan_content('description'); }
function bilyan_money($value) { return bilyan_setting('currency', 'USD') . ' ' . number_format_i18n((float) $value, 2); }
function bilyan_icon($name, $class = '') {
    $paths = [
        'cart' => '<path d="M3 3h2l2.4 12h11.2l2-8H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'arrow' => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
        'pin' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'box' => '<path d="m3 7 9-5 9 5v10l-9 5-9-5V7Zm0 0 9 5 9-5M12 12v10M7.5 4.5l9 5"/>',
        'headphones' => '<path d="M4 14v-3a8 8 0 0 1 16 0v3"/><rect x="3" y="12" width="4" height="8" rx="2"/><rect x="17" y="12" width="4" height="8" rx="2"/>',
        'home' => '<path d="m3 10 9-8 9 8v11H3V10Zm6 11v-8h6v8"/>',
        'fashion' => '<path d="m8 3-6 4 3 5 3-2v11h8V10l3 2 3-5-6-4c-1 4-7 4-8 0Z"/>',
        'beauty' => '<path d="M8 10h8v11H8zM10 10V4l4-2v8M6 21h12"/>',
        'sport' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3v18M5 5c8 4 8 10 14 14M19 5C11 9 11 15 5 19"/>',
        'service' => '<path d="M14 3a6 6 0 0 0-7 8L2 16l6 6 5-5a6 6 0 0 0 8-7l-4 4-5-5 4-4Z"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'heart' => '<path d="M20 5a5 5 0 0 0-8 1 5 5 0 0 0-8-1c-5 6 8 15 8 15S25 11 20 5Z"/>',
    ];
    return '<svg class="icon ' . esc_attr($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['box']) . '</svg>';
}
function bilyan_logo() {
    $url = get_template_directory_uri() . '/assets/images/logo.jpg';
    if (has_custom_logo()) $url = wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'medium');
    echo '<a class="brand' . (has_custom_logo() ? ' brand-custom' : '') . '" href="' . esc_url(home_url('/')) . '" aria-label="' . esc_attr(get_bloginfo('name') . ' home') . '"><img src="' . esc_url($url) . '" alt="' . esc_attr(get_bloginfo('name')) . '" width="100" height="100"></a>';
}
function bilyan_product_image($id, $size = 'medium_large') {
    if (has_post_thumbnail($id)) return get_the_post_thumbnail_url($id, $size);
    $demo = get_post_meta($id, '_bilyan_demo_image', true);
    $allowed = ['headphones', 'bag', 'chair', 'lamp', 'watch', 'bottle'];
    return get_template_directory_uri() . '/assets/images/' . (in_array($demo, $allowed, true) ? $demo : 'placeholder') . '.svg';
}
function bilyan_category_image($term) {
    $term = is_object($term) ? $term : get_term($term, 'bilyan_category');
    if (!$term || is_wp_error($term)) return get_template_directory_uri() . '/assets/images/placeholder.svg';
    $custom = get_term_meta($term->term_id, '_bilyan_category_image', true);
    if ($custom && filter_var($custom, FILTER_VALIDATE_URL)) return esc_url($custom);
    $name = strtolower($term->name);
    $demo = str_contains($name, 'elect') ? 'headphones' : (str_contains($name, 'fashion') || str_contains($name, 'access') ? 'bag' : (str_contains($name, 'home') ? 'chair' : (str_contains($name, 'sport') || str_contains($name, 'outdoor') ? 'bottle' : 'lamp')));
    return get_template_directory_uri() . '/assets/images/' . $demo . '.svg';
}
function bilyan_product_card($id) {
    $price = get_post_meta($id, '_bilyan_price', true);
    $available = get_post_meta($id, '_bilyan_unavailable', true) !== '1';
    $terms = get_the_terms($id, 'bilyan_category');
    ?>
    <article class="product-card">
        <a class="product-image" href="<?php echo esc_url(get_permalink($id)); ?>"><img loading="lazy" src="<?php echo esc_url(bilyan_product_image($id)); ?>" alt="<?php echo esc_attr(get_the_title($id)); ?>" width="600" height="600"><?php if (!$available): ?><span class="product-badge">Unavailable</span><?php endif; ?></a>
        <div class="product-info"><span class="eyebrow muted"><?php echo esc_html($terms && !is_wp_error($terms) ? $terms[0]->name : 'Everyday essentials'); ?></span>
        <h3><a href="<?php echo esc_url(get_permalink($id)); ?>"><?php echo esc_html(get_the_title($id)); ?></a></h3>
        <p><?php echo esc_html(wp_trim_words(get_the_excerpt($id), 12)); ?></p>
        <div class="product-bottom"><strong><?php echo esc_html($price !== '' ? bilyan_money($price) : ''); ?></strong><button class="add-button" type="button" data-add="<?php echo esc_attr($id); ?>" aria-label="<?php echo esc_attr('Add ' . get_the_title($id) . ' to cart'); ?>" <?php disabled(!$available); ?>><?php echo bilyan_icon('cart'); ?></button></div></div>
    </article>
    <?php
}
function bilyan_search_form() { ?>
    <form class="search-form" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search"><label class="screen-reader-text" for="product-search">Search products</label><input id="product-search" name="s" type="search" placeholder="What are you looking for?" value="<?php echo esc_attr(get_search_query()); ?>"><input type="hidden" name="post_type" value="bilyan_product"><button aria-label="Search products"><?php echo bilyan_icon('search'); ?></button></form>
<?php }
