<!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo(
    "charset",
); ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><?php wp_head(); ?></head><body <?php body_class(); ?>><?php wp_body_open(); ?><a class="skip-link" href="#main">Skip to content</a><div class="noticebar">A fictional portfolio store · Free UAE standard delivery from AED 250</div><header class="site-header"><a class="brand" href="<?php echo esc_url(
    home_url("/"),
); ?>" aria-label="Nisma home">nisma<span>THOUGHTFUL EVERYDAY</span></a><nav aria-label="Main navigation"><a href="<?php echo esc_url(
    wc_get_page_permalink("shop"),
); ?>">Shop all</a><a href="<?php echo esc_url(
    nisma_url("categories"),
); ?>">Collections</a><a href="<?php echo esc_url(
    nisma_url("about"),
); ?>">Our story</a></nav><div class="header-actions"><a href="<?php echo esc_url(
    nisma_url("wishlist"),
); ?>">Saved</a><a href="<?php echo esc_url(
    wc_get_page_permalink("myaccount"),
); ?>">Account</a><a href="<?php echo esc_url(
    wc_get_cart_url(),
); ?>">Bag <span class="bag-count"><?php echo WC()->cart
    ? absint(WC()->cart->get_cart_contents_count())
    : 0; ?></span></a></div></header><div id="store-status" class="sr-only" role="status" aria-live="polite"></div>
