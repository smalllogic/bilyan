<?php
// Render real WordPress templates locally; never send mail or alter live product data.
if (PHP_SAPI !== 'cli' || empty($argv[1])) exit("Usage: php product-render.php LOCAL_WORDPRESS_PATH\n");
require rtrim($argv[1], '/') . '/wp-load.php';
if (wp_get_environment_type() !== 'local') exit("Local test installations only.\n");
$product = get_page_by_path('sample-headphones', OBJECT, 'bilyan_product');
if (!$product) exit("Import sample products first.\n");
$id = $product->ID;
$price = get_post_meta($id, '_bilyan_price', true);
$unavailable = get_post_meta($id, '_bilyan_unavailable', true);
function render_product($id) {
    query_posts(['post_type' => 'bilyan_product', 'p' => $id]);
    ob_start(); require get_template_directory() . '/single-bilyan_product.php'; $html = ob_get_clean();
    wp_reset_query();
    return $html;
}
function check_product($condition, $label) {
    if (!$condition) throw new RuntimeException($label);
    echo "PASS: $label\n";
}
try {
    update_post_meta($id, '_bilyan_price', '49.00');
    update_post_meta($id, '_bilyan_unavailable', '0');
    $html = render_product($id);
    check_product(str_contains($html, 'assets/product.css?ver=1.3.0'), 'Dedicated product stylesheet loads with new cache version');
    check_product(str_contains($html, 'class="detail-price">USD 49.00'), 'Priced product displays its price');
    check_product(str_contains($html, 'View full image') && str_contains($html, 'product-information-title'), 'Image and detail sections render');
    check_product(str_contains($html, 'data-quantity-input="product-quantity"'), 'Quantity control stays connected to cart button');
    update_post_meta($id, '_bilyan_price', '');
    $html = render_product($id);
    check_product(!str_contains($html, 'Ask for a quote') && !str_contains($html, 'class="detail-price"'), 'Unpriced product has no price placeholder');
    check_product(str_contains($html, 'data-add="' . $id . '"'), 'Unpriced product can still be added');
    ob_start(); bilyan_product_card($id); $card = ob_get_clean();
    check_product(!str_contains($card, 'Ask for a quote'), 'Product card has no price placeholder');
    update_post_meta($id, '_bilyan_unavailable', '1');
    $html = render_product($id);
    check_product((bool) preg_match('/<button[^>]*data-add="' . $id . '"[^>]*disabled/', $html), 'Unavailable product cannot be added');
    check_product(str_contains($html, 'Currently unavailable'), 'Unavailable status remains visible');
    echo "All 9 product rendering checks passed.\n";
} finally {
    update_post_meta($id, '_bilyan_price', $price);
    update_post_meta($id, '_bilyan_unavailable', $unavailable);
}
