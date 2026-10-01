<footer class="site-footer"><div><a class="brand" href="<?php echo esc_url(
    home_url("/"),
); ?>">nisma</a><p>Thoughtful essentials.<br>A little intention, every day.</p><small>© <?php echo esc_html(
    wp_date("Y"),
); ?> Nisma<br>Fictional portfolio concept · No real purchases</small></div><nav aria-label="Shop footer"><h2>Explore</h2><?php foreach (
     [
         "shop" => "Shop all",
         "categories" => "Collections",
         "wishlist" => "Saved pieces",
         "about" => "Our story",
         "case-study" => "Project case study",
     ]
     as $slug => $label
 ) { ?><a href="<?php echo esc_url(nisma_url($slug)); ?>"><?php echo esc_html(
    $label,
); ?></a><?php } ?></nav><nav aria-label="Help footer"><h2>Here to help</h2><?php foreach (
    [
        "contact" => "Contact",
        "faq" => "FAQ",
        "shipping-returns" => "Shipping & returns",
        "privacy" => "Privacy",
        "terms" => "Terms",
        "photo-credits" => "Photo credits",
    ]
    as $slug => $label
) { ?><a href="<?php echo esc_url(nisma_url($slug)); ?>"><?php echo esc_html(
    $label,
); ?></a><?php } ?></nav><div class="footer-note"><h2>Across the UAE</h2><p>Standard · 2–4 business days<br>Express · 1–2 business days<br>Sample delivery estimates</p><p>Secure local test checkout<br>No payment details requested</p></div></footer><?php wp_footer(); ?></body></html>
