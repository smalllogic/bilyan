<?php
defined('ABSPATH') || exit;
get_header();
while (have_posts()):
    the_post();
    $id = get_the_ID();
    $price = get_post_meta($id, '_bilyan_price', true);
    $sku = get_post_meta($id, '_bilyan_sku', true);
    $available = get_post_meta($id, '_bilyan_unavailable', true) !== '1';
?>
<div class="container product-page">
    <div class="breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span><a href="<?php echo esc_url(get_post_type_archive_link('bilyan_product')); ?>">Shop</a><span>/</span><span><?php the_title(); ?></span></div>
    <?php if (post_password_required()): echo get_the_password_form(); else: ?>
    <article class="product-detail">
        <div class="product-visual">
            <a class="detail-image" href="<?php echo esc_url(bilyan_product_image($id, 'full')); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr('View full image of ' . get_the_title()); ?>">
                <span class="image-brand">BILYAN <span>EVERYDAY FINDS</span></span>
                <img src="<?php echo esc_url(bilyan_product_image($id, 'large')); ?>" alt="<?php the_title_attribute(); ?>" width="800" height="800" fetchpriority="high">
                <span class="image-expand"><?php echo bilyan_icon('search'); ?><span>View full image</span></span>
            </a>
            <div class="visual-caption"><span>Something for your everyday.</span><span>All in one place.</span></div>
        </div>
        <div class="detail-copy">
            <div class="detail-category"><?php echo wp_kses_post(get_the_term_list($id, 'bilyan_category', '', ' / ')); ?></div>
            <h1><?php the_title(); ?></h1>
            <div class="detail-excerpt"><?php the_excerpt(); ?></div>
            <div class="product-buy-box">
                <div class="detail-price-row">
                    <?php if ($price !== ''): ?><p class="detail-price"><?php echo esc_html(bilyan_money($price)); ?></p><?php endif; ?>
                    <span class="availability <?php echo !$available ? 'is-unavailable' : ''; ?>"><span></span><?php echo $available ? 'Available to order' : 'Currently unavailable'; ?></span>
                </div>
                <label class="quantity-label" for="product-quantity">Quantity <span>Select how many you need</span></label>
                <div class="detail-actions">
                    <input type="number" min="1" max="99" value="1" id="product-quantity" <?php disabled(!$available); ?>>
                    <button type="button" class="button" data-add="<?php echo esc_attr($id); ?>" data-quantity-input="product-quantity" <?php disabled(!$available); ?>><?php echo bilyan_icon('cart'); ?> Add to cart <?php echo bilyan_icon('arrow'); ?></button>
                </div>
                <p class="order-note"><?php echo bilyan_icon('check'); ?><span>No online payment. Send your cart when you’re ready.</span></p>
            </div>
            <div class="detail-services">
                <div><?php echo bilyan_icon('box'); ?><span><strong>Order with confidence</strong><small>We’ll confirm availability and final pricing.</small></span></div>
                <div><?php echo bilyan_icon('pin'); ?><span><strong>Delivery, made personal</strong><small>Share your area. We’ll arrange the details with you.</small></span></div>
            </div>
            <a class="product-help" href="<?php echo esc_url('mailto:' . bilyan_email() . '?subject=' . rawurlencode('Question about ' . get_the_title())); ?>"><?php echo bilyan_icon('headphones'); ?><span><strong>A question before you choose?</strong><small>Talk to the Bilyan team</small></span><?php echo bilyan_icon('arrow'); ?></a>
            <?php if ($sku): ?><p class="detail-sku">PRODUCT CODE <span><?php echo esc_html($sku); ?></span></p><?php endif; ?>
        </div>
    </article>
    <section class="product-information" aria-labelledby="product-information-title">
        <div class="product-description prose"><span class="eyebrow">A CLOSER LOOK</span><h2 id="product-information-title">The details that matter.</h2><?php the_content(); wp_link_pages(); ?></div>
        <aside class="product-order-guide"><span class="eyebrow">SIMPLE FROM THE START</span><h3>Found your next favorite?</h3><ol><li><span>01</span><div><strong>Make it your cart</strong><p>Choose your products and quantities.</p></div></li><li><span>02</span><div><strong>Send your request</strong><p>Add your contact and delivery details.</p></div></li><li><span>03</span><div><strong>We’ll take it from here</strong><p>Our team will confirm your order with you.</p></div></li></ol><a class="text-link" href="<?php echo esc_url(bilyan_page_url('terms')); ?>">Ordering & delivery <?php echo bilyan_icon('arrow'); ?></a></aside>
    </section>
    <section class="product-questions" aria-label="Product ordering questions">
        <details><summary>How does ordering work?<span aria-hidden="true">+</span></summary><p>Add this product to your cart, choose your quantity, then send your request with your contact details. No payment is collected on this website.</p></details>
        <details><summary>What about delivery?<span aria-hidden="true">+</span></summary><p>Include your delivery address when sending your request. Our team will confirm delivery availability and any charges before you confirm the order.</p></details>
    </section>
    <?php
    $related_args = ['post_type' => 'bilyan_product', 'posts_per_page' => 4, 'post__not_in' => [$id], 'has_password' => false];
    $categories = wp_get_post_terms($id, 'bilyan_category', ['fields' => 'ids']);
    if (!is_wp_error($categories) && $categories) $related_args['tax_query'] = [['taxonomy' => 'bilyan_category', 'field' => 'term_id', 'terms' => $categories]];
    $related = new WP_Query($related_args);
    if (!$related->have_posts()) { unset($related_args['tax_query']); $related = new WP_Query($related_args); }
    if ($related->have_posts()): ?>
    <section class="section product-related"><div class="section-heading"><div><span class="eyebrow">KEEP DISCOVERING</span><h2>A few more good finds.</h2></div><a class="text-link" href="<?php echo esc_url(get_post_type_archive_link('bilyan_product')); ?>">Explore the shop <?php echo bilyan_icon('arrow'); ?></a></div><div class="product-grid"><?php while ($related->have_posts()): $related->the_post(); bilyan_product_card(get_the_ID()); endwhile; ?></div></section>
    <?php endif; wp_reset_postdata(); endif; ?>
</div>
<?php endwhile; get_footer(); ?>
