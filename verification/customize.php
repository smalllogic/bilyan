<?php
// Runs only in the disposable local WordPress database; restores changes in finally.
if (PHP_SAPI !== 'cli' || empty($argv[1])) exit(1);
require rtrim($argv[1], '/') . '/wp-load.php';
if (wp_get_environment_type() !== 'local') exit("Local only.\n");
function check($ok, $name) { if (!$ok) throw new RuntimeException($name); echo "PASS: $name\n"; }
function render_page($path) {
    $ch = curl_init('http://127.0.0.1:8765' . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20]);
    $body = curl_exec($ch);
    check($body !== false && curl_getinfo($ch, CURLINFO_RESPONSE_CODE) === 200, 'HTTP 200 ' . $path);
    return $body;
}
$original = get_theme_mods();
$about = get_page_by_path('about');
$attachment = 0;
try {
    require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
    $manager = new WP_Customize_Manager();
    do_action('customize_register', $manager);
    foreach (bilyan_content_fields() as $key => $field) {
        $setting = $manager->get_setting('bilyan_' . $key);
        check($setting && $manager->get_control('bilyan_' . $key), 'Registered: ' . $key);
    }
    check($manager->get_setting('bilyan_primary_color')->sanitize('bad') === '#bf0008', 'Invalid color falls back');
    check($manager->get_setting('bilyan_hero_url')->sanitize('javascript:alert(1)') === '', 'Unsafe URL rejected');
    check($manager->get_setting('bilyan_featured_count')->sanitize(999) === 12, 'Product count bounded');
    check($manager->get_setting('bilyan_show_hero')->sanitize('false') === false, 'Checkbox false handled');
    check(bilyan_palette('#fff')['on'] === '#000000' && bilyan_palette('#000')['on'] === '#ffffff', 'Light and dark foregrounds');
    set_theme_mod('bilyan_primary_color', '#176840');
    set_theme_mod('bilyan_hero_title', '<script>alert(1)</script>Custom & title');
    set_theme_mod('bilyan_hero_url', 'https://example.org/catalog');
    set_theme_mod('bilyan_footer_note', '');
    $body = render_page('/');
    check(str_contains($body, '--red:#176840'), 'Palette reaches frontend');
    check(str_contains($body, 'Custom &amp; title') && !str_contains($body, '<script>alert(1)</script>'), 'Text safely escaped');
    check(str_contains($body, 'https://example.org/catalog'), 'Custom button URL rendered');
    check(!str_contains($body, 'Made for your everyday.'), 'Empty copy stays empty');
    $upload = wp_upload_bits('bilyan-qa-logo.jpg', null, file_get_contents(get_template_directory() . '/assets/images/logo.jpg'));
    check(empty($upload['error']), 'Test image uploaded');
    $attachment = wp_insert_attachment(['post_title' => 'QA image', 'post_mime_type' => 'image/jpeg', 'post_status' => 'inherit'], $upload['file']);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    wp_update_attachment_metadata($attachment, wp_generate_attachment_metadata($attachment, $upload['file']));
    update_post_meta($attachment, '_wp_attachment_image_alt', 'QA image alt');
    foreach (['hero_image', 'about_image', 'story_image'] as $key) set_theme_mod('bilyan_' . $key, $attachment);
    $body = render_page('/');
    check(str_contains($body, 'QA image alt') && str_contains($body, 'class="story-image"'), 'Homepage images render');
    $body = render_page('/?page_id=' . $about->ID);
    check(str_contains($body, 'about-custom-image'), 'About image renders');
    set_theme_mod('bilyan_about_editor', true);
    $body = render_page('/?page_id=' . $about->ID);
    check(!str_contains($body, 'about-layout') && str_contains($body, 'standard-page'), 'About editor replaces built-in layout');
    foreach (['hero', 'benefits', 'categories', 'featured', 'story', 'steps'] as $key) set_theme_mod('bilyan_show_' . $key, false);
    set_theme_mod('bilyan_show_topbar', false);
    $body = render_page('/');
    foreach (['hero container', 'container benefits', 'front-page-categories', 'product-grid', 'story-banner', 'how-it-works', 'class="topbar"'] as $marker) check(!str_contains($body, $marker), 'Hidden: ' . $marker);
    echo "All customization checks passed.\n";
} finally {
    update_option('theme_mods_' . get_option('stylesheet'), $original);
    if ($attachment) wp_delete_attachment($attachment, true);
}
