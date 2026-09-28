<?php
defined('ABSPATH') || exit;
function bilyan_content_fields() {
    return [
        'hero_eyebrow' => ['label' => '首屏眉题', 'default' => 'WELCOME TO BILYAN', 'section' => 'home', 'type' => 'text'],
        'hero_title' => ['label' => '首屏标题', 'default' => 'A little of everything.', 'section' => 'home', 'type' => 'text'],
        'hero_accent' => ['label' => '首屏强调标题', 'default' => 'All in one place.', 'section' => 'home', 'type' => 'text'],
        'hero_description' => ['label' => '首屏简介', 'default' => 'For your home, your style, and your everyday.
Discover products you’ll love, in one convenient place.', 'section' => 'home', 'type' => 'textarea'],
        'hero_button' => ['label' => '首屏按钮文字', 'default' => 'Explore the shop', 'section' => 'home', 'type' => 'text'],
        'hero_caption' => ['label' => '首屏提示', 'default' => 'Choose your favorites. We’ll take it from there.', 'section' => 'home', 'type' => 'text'],
        'hero_art_label' => ['label' => '图片旁文案', 'default' => 'GOOD FINDS.
EVERY DAY.', 'section' => 'home', 'type' => 'textarea'],
        'hero_tag' => ['label' => '图片卡片文案', 'default' => 'Something for everyone.', 'section' => 'home', 'type' => 'text'],
        'hero_tag_strong' => ['label' => '图片卡片强调文字', 'default' => 'That’s the Bilyan way.', 'section' => 'home', 'type' => 'text'],
        'categories_eyebrow' => ['label' => '分类眉题', 'default' => 'FIND YOUR EVERYDAY', 'section' => 'home', 'type' => 'text'],
        'categories_title' => ['label' => '分类标题', 'default' => 'Shop by category', 'section' => 'home', 'type' => 'text'],
        'categories_button' => ['label' => '分类链接文字', 'default' => 'Explore all products', 'section' => 'home', 'type' => 'text'],
        'featured_eyebrow' => ['label' => '精选眉题', 'default' => 'A FEW THINGS YOU’LL LOVE', 'section' => 'home', 'type' => 'text'],
        'featured_title' => ['label' => '精选标题', 'default' => 'Everyday favorites', 'section' => 'home', 'type' => 'text'],
        'featured_button' => ['label' => '精选链接文字', 'default' => 'View all products', 'section' => 'home', 'type' => 'text'],
        'story_eyebrow' => ['label' => '品牌横幅眉题', 'default' => 'LESS SEARCHING. MORE FINDING.', 'section' => 'home', 'type' => 'text'],
        'story_title' => ['label' => '品牌横幅标题', 'default' => 'Your everyday,
a little easier.', 'section' => 'home', 'type' => 'textarea'],
        'story_button' => ['label' => '品牌横幅按钮', 'default' => 'Meet Bilyan', 'section' => 'home', 'type' => 'text'],
        'steps_eyebrow' => ['label' => '购物步骤眉题', 'default' => 'FROM DISCOVERY TO YOUR DOOR', 'section' => 'home', 'type' => 'text'],
        'steps_title' => ['label' => '购物步骤标题', 'default' => 'A simpler way to shop', 'section' => 'home', 'type' => 'text'],
        'steps_note' => ['label' => '购物步骤提示', 'default' => 'No online payment. Just a conversation.', 'section' => 'home', 'type' => 'text'],
        'benefit_1_title' => ['label' => '服务亮点 1 标题', 'default' => 'So much to discover', 'section' => 'benefits', 'type' => 'text'],
        'benefit_1_body' => ['label' => '服务亮点 1 说明', 'default' => 'Everyday finds, in one place', 'section' => 'benefits', 'type' => 'text'],
        'benefit_2_title' => ['label' => '服务亮点 2 标题', 'default' => 'Ordering made simple', 'section' => 'benefits', 'type' => 'text'],
        'benefit_2_body' => ['label' => '服务亮点 2 说明', 'default' => 'Add to cart and send a request', 'section' => 'benefits', 'type' => 'text'],
        'benefit_3_title' => ['label' => '服务亮点 3 标题', 'default' => 'A real team to help', 'section' => 'benefits', 'type' => 'text'],
        'benefit_3_body' => ['label' => '服务亮点 3 说明', 'default' => 'We’ll confirm the details with you', 'section' => 'benefits', 'type' => 'text'],
        'benefit_4_title' => ['label' => '服务亮点 4 标题', 'default' => 'Rooted in Mogadishu', 'section' => 'benefits', 'type' => 'text'],
        'benefit_4_body' => ['label' => '服务亮点 4 说明', 'default' => 'Your local marketplace', 'section' => 'benefits', 'type' => 'text'],
        'step_1_title' => ['label' => '步骤 1 标题', 'default' => 'Find something you love', 'section' => 'benefits', 'type' => 'text'],
        'step_1_body' => ['label' => '步骤 1 说明', 'default' => 'Browse the collection and explore product details.', 'section' => 'benefits', 'type' => 'text'],
        'step_2_title' => ['label' => '步骤 2 标题', 'default' => 'Make it your cart', 'section' => 'benefits', 'type' => 'text'],
        'step_2_body' => ['label' => '步骤 2 说明', 'default' => 'Add your favorites and choose the quantities you need.', 'section' => 'benefits', 'type' => 'text'],
        'step_3_title' => ['label' => '步骤 3 标题', 'default' => 'Send. We’ll be in touch.', 'section' => 'benefits', 'type' => 'text'],
        'step_3_body' => ['label' => '步骤 3 说明', 'default' => 'Share your details. We’ll confirm availability, pricing and delivery.', 'section' => 'benefits', 'type' => 'text'],
        'announcement' => ['label' => '顶部公告', 'default' => 'Your everyday marketplace. All in one place.', 'section' => 'brand', 'type' => 'text'],
        'nav_note' => ['label' => '导航提示', 'default' => 'Discover. Add to cart. Send your order.', 'section' => 'brand', 'type' => 'text'],
        'help_label' => ['label' => '联系提示', 'default' => 'Need a hand?', 'section' => 'brand', 'type' => 'text'],
        'help_button' => ['label' => '联系文字', 'default' => 'Let’s talk', 'section' => 'brand', 'type' => 'text'],
        'footer_contact' => ['label' => '页脚联系说明', 'default' => 'Questions about a product or an order?
We’d love to hear from you.', 'section' => 'footer', 'type' => 'textarea'],
        'copyright' => ['label' => '版权文字（年份自动生成）', 'default' => 'Bilyan. All rights reserved.', 'section' => 'footer', 'type' => 'text'],
        'footer_tagline' => ['label' => '页脚标语', 'default' => 'All In One Place.', 'section' => 'footer', 'type' => 'text'],
        'footer_note' => ['label' => '页脚补充文字', 'default' => 'Made for your everyday.', 'section' => 'footer', 'type' => 'text'],
        'about_art_text' => ['label' => 'About 展示文字', 'default' => 'Many needs.
One place.
Bilyan.', 'section' => 'about', 'type' => 'textarea'],
        'about_eyebrow' => ['label' => 'About 眉题', 'default' => 'HELLO, WE’RE BILYAN', 'section' => 'about', 'type' => 'text'],
        'about_title' => ['label' => 'About 标题', 'default' => 'Life has a lot on its list.
We help you find it.', 'section' => 'about', 'type' => 'textarea'],
        'about_details' => ['label' => 'About 补充介绍', 'default' => 'Browse at your own pace, put together your cart, and send us your request. Our team will help with availability, product questions and delivery details.', 'section' => 'about', 'type' => 'textarea'],
        'about_button' => ['label' => 'About 按钮', 'default' => 'Find your next favorite', 'section' => 'about', 'type' => 'text'],
        'contact_eyebrow' => ['label' => '联系页眉题', 'default' => 'A REAL TEAM, READY TO HELP', 'section' => 'contact', 'type' => 'text'],
        'contact_title' => ['label' => '联系页标题', 'default' => 'Let’s talk.', 'section' => 'contact', 'type' => 'text'],
        'contact_intro' => ['label' => '联系页简介', 'default' => 'A product question? An order update?
We’re here to make things easier.', 'section' => 'contact', 'type' => 'textarea'],
        'contact_note_eyebrow' => ['label' => '提示眉题', 'default' => 'BEFORE YOU HIT SEND', 'section' => 'contact', 'type' => 'text'],
        'contact_note_title' => ['label' => '提示标题', 'default' => 'A few helpful details.', 'section' => 'contact', 'type' => 'text'],
        'contact_note_product' => ['label' => '产品咨询说明', 'default' => 'Asking about a product? Include its name or product code.', 'section' => 'contact', 'type' => 'textarea'],
        'contact_note_order' => ['label' => '订单咨询说明', 'default' => 'Following up on an order? Include your BLY order reference so we can find your request.', 'section' => 'contact', 'type' => 'textarea'],
        'faq_intro' => ['label' => 'FAQ 简介', 'default' => 'A few things to know about shopping with Bilyan.', 'section' => 'contact', 'type' => 'textarea'],
        'about_intro' => ['label' => 'About 品牌介绍', 'default' => 'We bring discovery and convenience together. Whether you’re looking for an everyday essential or something for your home, our goal is to make your next find a little easier.', 'section' => 'about', 'type' => 'textarea'],
        'description' => ['label' => '品牌简介', 'default' => 'Bilyan is an all-in-one marketplace connecting customers with a wide range of products and services in one convenient place.', 'section' => 'brand', 'type' => 'textarea'],
        'primary_color' => ['label' => '品牌主色', 'default' => '#bf0008', 'section' => 'colors', 'type' => 'color'],
        'surface_color' => ['label' => '区块背景色', 'default' => '#f6f3ee', 'section' => 'colors', 'type' => 'color'],
        'hero_image' => ['label' => '首屏图片', 'default' => 0, 'section' => 'home', 'type' => 'image'],
        'about_image' => ['label' => 'About 图片', 'default' => 0, 'section' => 'about', 'type' => 'image'],
        'story_image' => ['label' => '品牌横幅图片', 'default' => 0, 'section' => 'home', 'type' => 'image'],
        'about_editor' => ['label' => '使用页面编辑器完全替换 About 布局', 'default' => false, 'section' => 'about', 'type' => 'checkbox'],
        'show_topbar' => ['label' => '显示顶部公告', 'default' => true, 'section' => 'brand', 'type' => 'checkbox'],
        'hero_url' => ['label' => '首屏按钮链接（空白使用商品列表）', 'default' => '', 'section' => 'home', 'type' => 'url'],
        'story_url' => ['label' => '品牌横幅链接（空白使用 About）', 'default' => '', 'section' => 'home', 'type' => 'url'],
        'about_url' => ['label' => 'About 按钮链接（空白使用商品列表）', 'default' => '', 'section' => 'about', 'type' => 'url'],
        'featured_count' => ['label' => '首页商品数量', 'default' => 4, 'section' => 'home', 'type' => 'number'],
        'show_hero' => ['label' => '显示首屏', 'default' => true, 'section' => 'visibility', 'type' => 'checkbox'],
        'show_benefits' => ['label' => '显示服务亮点', 'default' => true, 'section' => 'visibility', 'type' => 'checkbox'],
        'show_categories' => ['label' => '显示商品分类', 'default' => true, 'section' => 'visibility', 'type' => 'checkbox'],
        'show_featured' => ['label' => '显示精选商品', 'default' => true, 'section' => 'visibility', 'type' => 'checkbox'],
        'show_story' => ['label' => '显示品牌横幅', 'default' => true, 'section' => 'visibility', 'type' => 'checkbox'],
        'show_steps' => ['label' => '显示购物步骤', 'default' => true, 'section' => 'visibility', 'type' => 'checkbox'],
    ];
}

// One schema drives defaults, validation and the native WordPress controls.
function bilyan_sanitize_content($value, $setting) {
    $key = preg_replace('/^bilyan_/', '', $setting->id);
    $field = bilyan_content_fields()[$key] ?? null;
    if (!$field || !is_scalar($value)) return $field['default'] ?? '';
    switch ($field['type']) {
        case 'checkbox': return in_array($value, [true, 1, '1'], true);
        case 'color': return sanitize_hex_color($value) ?: $field['default'];
        case 'image': $id = absint($value); return wp_attachment_is_image($id) ? $id : 0;
        case 'number': return max(1, min(12, absint($value)));
        case 'url': return esc_url_raw($value, ['http', 'https']);
        case 'textarea': return sanitize_textarea_field($value);
        default: return sanitize_text_field($value);
    }
}
function bilyan_content($key) {
    $field = bilyan_content_fields()[$key] ?? null;
    if (!$field) return '';
    return bilyan_sanitize_content(get_theme_mod('bilyan_' . $key, $field['default']), (object) ['id' => 'bilyan_' . $key]);
}
function bilyan_content_image($key, $fallback = '') {
    $id = bilyan_content($key);
    return $id ? (wp_get_attachment_image_url($id, 'full') ?: $fallback) : $fallback;
}
add_action('customize_register', function ($manager) {
    $manager->add_panel('bilyan_content', ['title' => 'Bilyan · 品牌与静态内容', 'priority' => 30, 'description' => '图片从媒体库选择；文字支持留空。发布后前台生效。Logo 和站点图标在「站点身份」中设置。']);
    foreach (['brand' => '品牌与顶部公告', 'colors' => '全站配色', 'home' => '首页内容与图片', 'benefits' => '服务亮点与购物步骤', 'visibility' => '首页区块显示', 'about' => 'About Us 页面', 'contact' => '联系与 FAQ 文案', 'footer' => '页脚内容'] as $id => $title) {
        $manager->add_section('bilyan_' . $id, ['title' => $title, 'panel' => 'bilyan_content']);
    }
    foreach (bilyan_content_fields() as $key => $field) {
        $id = 'bilyan_' . $key;
        $manager->add_setting($id, ['default' => $field['default'], 'sanitize_callback' => 'bilyan_sanitize_content', 'transport' => 'refresh', 'capability' => 'edit_theme_options']);
        $args = ['label' => $field['label'], 'section' => 'bilyan_' . $field['section'], 'type' => $field['type']];
        if ($field['type'] === 'image') {
            $args['mime_type'] = 'image';
            $args['description'] = '支持媒体库中的图片。请在媒体库填写替代文本；移除图片后恢复默认展示。';
            $manager->add_control(new WP_Customize_Media_Control($manager, $id, $args));
        } elseif ($field['type'] === 'color') {
            $manager->add_control(new WP_Customize_Color_Control($manager, $id, $args));
        } else {
            if ($field['type'] === 'number') $args['input_attrs'] = ['min' => 1, 'max' => 12, 'step' => 1];
            $manager->add_control($id, $args);
        }
    }
});
// Derive hover, tint and readable foreground colors from a single brand color.
function bilyan_palette($hex) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) $hex = preg_replace('/(.)/', '$1$1', $hex);
    $rgb = array_map('hexdec', str_split($hex, 2));
    $mix = function ($target, $amount) use ($rgb) {
        return sprintf('#%02x%02x%02x', ...array_map(fn($c) => (int) round($c + ($target - $c) * $amount), $rgb));
    };
    $linear = array_map(fn($c) => ($c / 255 <= .04045) ? $c / 255 / 12.92 : pow(($c / 255 + .055) / 1.055, 2.4), $rgb);
    $luminance = .2126 * $linear[0] + .7152 * $linear[1] + .0722 * $linear[2];
    return ['dark' => $mix(0, .2), 'soft' => $mix(255, .92), 'border' => $mix(255, .60), 'on' => $luminance > .179 ? '#000000' : '#ffffff'];
}
add_action('wp_enqueue_scripts', function () {
    $color = bilyan_content('primary_color');
    $palette = bilyan_palette($color);
    $hover = bilyan_palette($palette['dark']);
    wp_enqueue_style('bilyan-customize', get_template_directory_uri() . '/assets/customize.css', ['bilyan'], BILYAN_VERSION);
    wp_add_inline_style('bilyan-customize', ':root{--red:' . $color . ';--red-dark:' . $palette['dark'] . ';--brand-soft:' . $palette['soft'] . ';--brand-border:' . $palette['border'] . ';--on-brand:' . $palette['on'] . ';--on-brand-hover:' . $hover['on'] . ';--cream:' . bilyan_content('surface_color') . ';}');
}, 30);
