<?php
/** Local integration tests. Run only against the disposable WordPress install.
 * php integration.php /absolute/path/to/wordpress http://127.0.0.1:8765
 * This suite stores test orders in that database. The test mail filter MUST be installed.
 */
if (PHP_SAPI !== 'cli' || empty($argv[1]) || empty($argv[2])) exit("Usage: php integration.php WORDPRESS_PATH BASE_URL\n");
require rtrim($argv[1], '/') . '/wp-load.php';
if (wp_get_environment_type() !== 'local' || !file_exists(WPMU_PLUGIN_DIR . '/bilyan-test-mail.php')) exit("Refusing to test without the local mail blocker.\n");
$base = rtrim($argv[2], '/');
$passed = 0;
function expect($condition, $message) {
    global $passed;
    if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
    $passed++; echo "PASS: $message\n";
}
function request($path, $data = null) {
    global $base;
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_FOLLOWLOCATION => true]);
    if ($data !== null) curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($data)]);
    $body = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE); $error = curl_error($ch);
    if ($body === false) throw new RuntimeException($error);
    return [$status, $body, json_decode($body, true)];
}
function ajax($action, $data = []) { return request('/wp-admin/admin-ajax.php', array_merge(['action' => $action], $data)); }
expect(wp_get_theme()->get('Name') === 'Bilyan', 'Theme is active');
expect(wp_count_posts('bilyan_product')->publish >= 6, 'Sample products imported');
$before = (int) wp_count_posts('bilyan_product')->publish; bilyan_import_demo();
expect((int) wp_count_posts('bilyan_product')->publish === $before, 'Demo importer does not duplicate products');
foreach (['cart', 'about', 'contact', 'faq', 'privacy', 'terms'] as $slug) {
    $page = get_page_by_path($slug);
    expect((bool) $page, "Page created: $slug");
    [$status, $body] = request('/?page_id=' . $page->ID);
    expect($status === 200 && str_contains($body, '</html>') && !str_contains($body, 'Fatal error'), "Page renders: $slug");
}
[$status, $body] = request('/');
expect($status === 200 && str_contains($body, 'All in one place.') && str_contains($body, 'headphones.svg'), 'Homepage and bundled assets render');
[$status, $body] = request('/?post_type=bilyan_product');
expect($status === 200 && str_contains($body, 'The everyday collection'), 'Shop archive renders');
[$status, $body] = request('/?s=Wireless&post_type=bilyan_product');
expect($status === 200 && str_contains($body, 'Wireless headphones') && str_contains($body, '1 products'), 'Product search filters results');
[$status, $body] = request('/?s=nomatch_xyz&post_type=bilyan_product');
expect($status === 200 && str_contains($body, 'No products found'), 'Empty search state');
$product = get_page_by_path('sample-headphones', OBJECT, 'bilyan_product');
$product_id = $product->ID;
[$status, $body] = request('/?bilyan_product=sample-headphones');
expect($status === 200 && str_contains($body, 'product-quantity') && str_contains($body, 'DEMO-001'), 'Product detail with quantity, SKU and image renders');
$terms = get_the_terms($product_id, 'bilyan_category');
[$status, $body] = request('/?bilyan_category=' . $terms[0]->slug);
expect($status === 200 && str_contains($body, 'Wireless headphones'), 'Category archive renders');
[$status, $body] = request('/?p=9999999');
expect($status === 404 && str_contains($body, 'This page has wandered off.'), '404 template and HTTP status');
[$status, , $data] = ajax('bilyan_cart', ['cart' => json_encode([['id' => $product_id, 'quantity' => 2, 'price' => 1]])]);
expect($status === 200 && $data['success'] && $data['data']['total'] === 9800, 'Server price wins over client price tampering');
$nonce = $data['data']['nonce'];
expect((bool) $nonce, 'Fresh checkout nonce returned');
foreach ([0, -1, 100, 1.5] as $qty) {
    [$status, , $data] = ajax('bilyan_cart', ['cart' => json_encode([['id' => $product_id, 'quantity' => $qty]])]);
    expect($status === 400 && !$data['success'], 'Reject invalid quantity: ' . $qty);
}
[$status, , $data] = ajax('bilyan_cart', ['cart' => '["malformed"]']);
expect($status === 400, 'Reject malformed cart');
[$status, , $data] = ajax('bilyan_cart', ['cart' => '[]']);
expect($status === 200 && $data['data']['items'] === [], 'Empty cart returns a valid empty state');
$values = ['nonce' => $nonce, 'cart' => json_encode([['id' => $product_id, 'quantity' => 2]]), 'name' => 'Local QA Customer', 'email' => 'qa@example.invalid', 'phone' => '+252 610 000 000', 'address' => 'Local test, Mogadishu', 'notes' => 'This is a test order. Do not contact.', 'consent' => '1', 'website' => '', 'request_id' => wp_generate_uuid4()];
[$status, , $data] = ajax('bilyan_order', array_merge($values, ['nonce' => 'invalid']));
expect($status === 403, 'Reject invalid nonce');
[$status, , $data] = ajax('bilyan_order', array_merge($values, ['email' => 'invalid']));
expect($status === 400, 'Reject invalid customer email');
[$status, , $data] = ajax('bilyan_order', array_merge($values, ['consent' => '0']));
expect($status === 400, 'Require privacy consent');
[$status, , $data] = ajax('bilyan_order', array_merge($values, ['website' => 'spam']));
expect($status === 400, 'Reject honeypot submission');
[$status, , $data] = ajax('bilyan_order', array_merge($values, ['cart' => '[]']));
expect($status === 400, 'Reject empty order');
update_post_meta($product_id, '_bilyan_unavailable', '1');
[$status, , $data] = ajax('bilyan_order', $values);
expect($status === 400, 'Reject unavailable product');
update_post_meta($product_id, '_bilyan_unavailable', '0');
wp_update_post(['ID' => $product_id, 'post_status' => 'draft']);
[$status, , $data] = ajax('bilyan_cart', ['cart' => $values['cart']]);
expect($status === 200 && !$data['data']['items'][0]['available'], 'Unpublished products are unavailable');
wp_update_post(['ID' => $product_id, 'post_status' => 'publish']);
[$status, , $data] = ajax('bilyan_order', $values);
expect($status === 200 && $data['success'] && str_starts_with($data['data']['reference'], 'BLY-'), 'Order is saved successfully even when mail fails');
$reference = $data['data']['reference']; $id = (int) substr($reference, strrpos($reference, '-') + 1);
$saved = get_post_meta($id, '_bilyan_order', true);
expect($saved['cart']['total'] === 9800 && $saved['cart']['items'][0]['quantity'] === 2 && $saved['email'] === $values['email'], 'Order preserves validated customer, quantity and price snapshot');
expect(get_post_meta($id, '_bilyan_mail_sent', true) === '0', 'Email failure is recorded for administrators');
expect(get_post_status($id) === 'private', 'Orders are private');
[$status, , $data] = ajax('bilyan_order', $values);
expect($status === 200 && $data['data']['reference'] === $reference, 'Retry returns same reference without creating duplicate order');
[$status] = request('/?p=' . $id . '&post_type=bilyan_order');
expect($status === 404, 'Anonymous visitor cannot view order');
[$status, , $data] = request('/?rest_route=/wp/v2/bilyan_order');
expect($status === 404, 'Order data is not exposed in REST API');
update_post_meta($product_id, '_bilyan_price', '');
[$status, , $data] = ajax('bilyan_cart', ['cart' => $values['cart']]);
expect($status === 200 && $data['data']['needsQuote'] && $data['data']['total'] === 0, 'Unpriced products require a quote without a fabricated price');
update_post_meta($product_id, '_bilyan_price', '49.00');
$rate_key = 'bilyan_rate_' . hash_hmac('sha256', '127.0.0.1', wp_salt('nonce'));
set_transient($rate_key, 8, HOUR_IN_SECONDS);
[$status] = ajax('bilyan_order', array_merge($values, ['request_id' => wp_generate_uuid4()]));
expect($status === 429, 'Rate limit enforced');
delete_transient($rate_key);
echo "\nAll $passed integration checks passed. Mail transmission was blocked.\n";
