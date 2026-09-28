<?php
defined('ABSPATH') || exit;
// Both endpoints are public by design. Orders are validated and priced on the server.
foreach (['wp_ajax_', 'wp_ajax_nopriv_'] as $prefix) {
    add_action($prefix . 'bilyan_cart', 'bilyan_cart_endpoint');
    add_action($prefix . 'bilyan_order', 'bilyan_order_endpoint');
}
function bilyan_input($key) {
    return isset($_POST[$key]) && is_string($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
}
function bilyan_cart_lines($raw) {
    if (strlen($raw) > 16000) return new WP_Error('cart', 'Your cart is too large. Please use up to 50 different products.');
    $cart = json_decode($raw, true);
    if (!is_array($cart) || count($cart) > 50) return new WP_Error('cart', 'Please add between 1 and 50 different products.');
    $items = []; $total = 0; $quote = false;
    foreach ($cart as $item) {
        if (!is_array($item) || !isset($item['id'], $item['quantity']) || !is_numeric($item['id']) || !is_numeric($item['quantity']) || (int) $item['quantity'] != $item['quantity'] || (int) $item['id'] != $item['id']) return new WP_Error('cart', 'Invalid cart item. Please refresh your cart.');
        $id = (int) $item['id']; $qty = (int) $item['quantity'];
        if ($qty < 1 || $qty > 99 || isset($items[$id])) return new WP_Error('quantity', 'Choose a quantity between 1 and 99 for each product.');
        $product = get_post($id);
        $valid = $product && $product->post_type === 'bilyan_product' && $product->post_status === 'publish' && !$product->post_password && get_post_meta($id, '_bilyan_unavailable', true) !== '1';
        $price = $valid ? get_post_meta($id, '_bilyan_price', true) : '';
        $cents = $price !== '' ? (int) round((float) $price * 100) : null;
        $subtotal = $cents !== null ? $cents * $qty : null;
        if ($valid) { $total += $subtotal ?? 0; $quote = $quote || $price === ''; }
        $items[$id] = ['id' => $id, 'quantity' => $qty, 'available' => $valid, 'name' => $valid ? get_the_title($id) : 'Unavailable product', 'sku' => $valid ? get_post_meta($id, '_bilyan_sku', true) : '', 'image' => $valid ? bilyan_product_image($id) : '', 'url' => $valid ? get_permalink($id) : '', 'price' => $cents, 'subtotal' => $subtotal, 'priceLabel' => $cents !== null ? bilyan_money($cents / 100) : 'Quote on request', 'subtotalLabel' => $subtotal !== null ? bilyan_money($subtotal / 100) : 'Quote on request'];
    }
    return ['items' => array_values($items), 'total' => $total, 'totalLabel' => bilyan_money($total / 100), 'needsQuote' => $quote, 'currency' => bilyan_setting('currency', 'USD')];
}
function bilyan_cart_endpoint() {
    nocache_headers();
    $cart = bilyan_cart_lines(bilyan_input('cart'));
    if (is_wp_error($cart)) wp_send_json_error(['message' => $cart->get_error_message()], 400);
    $cart['nonce'] = wp_create_nonce('bilyan_order');
    wp_send_json_success($cart);
}
function bilyan_order_endpoint() {
    nocache_headers();
    if (!check_ajax_referer('bilyan_order', 'nonce', false)) wp_send_json_error(['message' => 'Your session expired. Refresh the page and try again.'], 403);
    if (bilyan_input('website') !== '') wp_send_json_error(['message' => 'Unable to send this request.'], 400);
    $name = sanitize_text_field(bilyan_input('name'));
    $email = sanitize_email(bilyan_input('email'));
    $phone = sanitize_text_field(bilyan_input('phone'));
    $address = sanitize_textarea_field(bilyan_input('address'));
    $notes = sanitize_textarea_field(bilyan_input('notes'));
    if (!$name || strlen($name) > 120 || !is_email($email) || strlen($email) > 190 || !preg_match('/^[0-9+() .-]{6,40}$/', $phone) || !$address || strlen($address) > 1000 || strlen($notes) > 2000 || bilyan_input('consent') !== '1') wp_send_json_error(['message' => 'Please provide your name, a valid email and phone number, your address, and privacy consent.'], 400);
    $cart = bilyan_cart_lines(bilyan_input('cart'));
    if (is_wp_error($cart)) wp_send_json_error(['message' => $cart->get_error_message()], 400);
    if (!$cart['items']) wp_send_json_error(['message' => 'Your cart is empty. Add a product first.'], 400);
    foreach ($cart['items'] as $item) if (!$item['available']) wp_send_json_error(['message' => 'A product is no longer available. Remove unavailable products from your cart.'], 400);
    $request = bilyan_input('request_id');
    if (!preg_match('/^[a-zA-Z0-9-]{20,80}$/', $request)) wp_send_json_error(['message' => 'Please refresh the page and try again.'], 400);
    $key = 'bilyan_request_' . hash('sha256', strtolower($email) . '|' . $request);
    $previous = get_option($key);
    if ($previous && isset($previous['reference'])) wp_send_json_success(['reference' => $previous['reference']]);
    // Atomic insert prevents duplicate simultaneous submissions for the same request.
    if (!add_option($key, ['pending' => time()], '', false)) wp_send_json_error(['message' => 'This request is already being processed. Wait a moment, then try again.'], 409);
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : 'unknown';
    $rate_key = 'bilyan_rate_' . hash_hmac('sha256', $ip, wp_salt('nonce'));
    $count = (int) get_transient($rate_key);
    if ($count >= 8) { delete_option($key); wp_send_json_error(['message' => 'Too many requests. Please try again in an hour or contact us by email.'], 429); }
    $id = wp_insert_post(['post_type' => 'bilyan_order', 'post_status' => 'private', 'post_title' => 'New order request'], true);
    if (is_wp_error($id)) { delete_option($key); wp_send_json_error(['message' => 'We could not save your order. Please try again.'], 500); }
    $reference = 'BLY-' . wp_date('Ymd') . '-' . $id;
    $data = ['name' => $name, 'email' => $email, 'phone' => $phone, 'address' => $address, 'notes' => $notes, 'cart' => $cart, 'reference' => $reference, 'consent_at' => current_time('mysql')];
    update_post_meta($id, '_bilyan_order', $data);
    update_post_meta($id, '_bilyan_status', 'new');
    wp_update_post(['ID' => $id, 'post_title' => $reference . ' — ' . $name]);
    update_option($key, ['reference' => $reference, 'created' => time()], false);
    wp_schedule_single_event(time() + 7 * DAY_IN_SECONDS, 'bilyan_expire_request', [$key]);
    set_transient($rate_key, $count + 1, HOUR_IN_SECONDS);
    $body = "New Bilyan order request: $reference\n\nCustomer: $name\nEmail: $email\nPhone: $phone\nAddress: $address\n\n";
    foreach ($cart['items'] as $item) $body .= $item['name'] . ' (' . $item['sku'] . ') × ' . $item['quantity'] . ' — ' . $item['subtotalLabel'] . "\n";
    $body .= "\nPriced items subtotal: " . $cart['totalLabel'] . ($cart['needsQuote'] ? "\nSome items need a quote." : '') . "\nDelivery and final availability to be confirmed. No payment collected.\n\nNotes: $notes\n\nManage order: " . admin_url('post.php?post=' . $id . '&action=edit');
    $sent = wp_mail(bilyan_email(), 'Bilyan order ' . $reference, $body, ['Reply-To: ' . $email]);
    update_post_meta($id, '_bilyan_mail_sent', $sent ? '1' : '0');
    wp_send_json_success(['reference' => $reference]);
}
add_action('bilyan_expire_request', function ($key) { if (str_starts_with($key, 'bilyan_request_')) delete_option($key); });
function bilyan_order_details($post) {
    $order = get_post_meta($post->ID, '_bilyan_order', true);
    if (!is_array($order)) { echo '<p>No order details.</p>'; return; }
    wp_nonce_field('bilyan_order_status', 'bilyan_status_nonce');
    echo '<p><strong>Reference:</strong> ' . esc_html($order['reference']) . '</p>';
    foreach (['name', 'email', 'phone', 'address', 'notes', 'consent_at'] as $key) echo '<p><strong>' . esc_html(ucwords(str_replace('_', ' ', $key))) . ':</strong><br>' . nl2br(esc_html($order[$key])) . '</p>';
    echo '<table class="widefat striped"><thead><tr><th>Product</th><th>SKU</th><th>Quantity</th><th>Subtotal</th></tr></thead><tbody>';
    foreach ($order['cart']['items'] as $item) echo '<tr><td>' . esc_html($item['name']) . '</td><td>' . esc_html($item['sku']) . '</td><td>' . esc_html($item['quantity']) . '</td><td>' . esc_html($item['subtotalLabel']) . '</td></tr>';
    echo '</tbody></table><p>Priced items subtotal: <strong>' . esc_html($order['cart']['totalLabel']) . '</strong>. ' . ($order['cart']['needsQuote'] ? 'Additional items need a quote. ' : '') . 'No payment collected.</p>';
    echo '<p><label>Order status <select name="bilyan_status">';
    foreach (['new', 'contacted', 'confirmed', 'completed', 'cancelled'] as $status) echo '<option value="' . esc_attr($status) . '" ' . selected(get_post_meta($post->ID, '_bilyan_status', true), $status, false) . '>' . esc_html(ucfirst($status)) . '</option>';
    echo '</select></label></p><p>Email notification: <strong>' . (get_post_meta($post->ID, '_bilyan_mail_sent', true) === '1' ? 'Accepted by mail server (delivery not guaranteed)' : 'Failed — configure SMTP and contact the customer from these details') . '</strong></p>';
}
add_action('save_post_bilyan_order', function ($id) {
    if (!current_user_can('manage_options') || !isset($_POST['bilyan_status_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bilyan_status_nonce'])), 'bilyan_order_status') || wp_is_post_autosave($id)) return;
    $status = sanitize_key($_POST['bilyan_status'] ?? '');
    if (in_array($status, ['new', 'contacted', 'confirmed', 'completed', 'cancelled'], true)) update_post_meta($id, '_bilyan_status', $status);
});
add_filter('manage_bilyan_order_posts_columns', function ($columns) { return ['cb' => $columns['cb'], 'title' => 'Order', 'bilyan_customer' => 'Customer', 'bilyan_status' => 'Status', 'bilyan_mail' => 'Email notification', 'date' => 'Date']; });
add_action('manage_bilyan_order_posts_custom_column', function ($column, $id) {
    $data = get_post_meta($id, '_bilyan_order', true);
    if ($column === 'bilyan_customer') echo esc_html(($data['email'] ?? '') . ' / ' . ($data['phone'] ?? ''));
    if ($column === 'bilyan_status') echo esc_html(ucfirst(get_post_meta($id, '_bilyan_status', true)));
    if ($column === 'bilyan_mail') echo get_post_meta($id, '_bilyan_mail_sent', true) === '1' ? 'Sent to mail server' : 'Failed — check SMTP';
}, 10, 2);
