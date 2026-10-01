<?php get_header(); ?><main id="main"><section class="hero"><div class="hero-copy"><span class="eyebrow">Made for the moments in between</span><h1>A softer pace.<br>A little intention.</h1><p>Thoughtful pieces for what you wear,<br>what you keep, and how you unwind.</p><a class="button" href="<?php echo esc_url(
    wc_get_page_permalink("shop"),
); ?>">Explore the collection <span aria-hidden="true">↗</span></a><p class="hero-footnote">SCARVES · STATIONERY · EVERYDAY RITUALS</p></div><div class="hero-image"><img fetchpriority="high" width="1200" height="1500" src="<?php echo esc_url(
    get_template_directory_uri() . "/images/scarf.webp",
); ?>" alt="A woman wearing a draped beige scarf — illustrative styling photograph"><a href="<?php echo esc_url(
    home_url("/?product_cat=scarves"),
); ?>">Soft layers, simple pleasures <span aria-hidden="true">↗</span></a></div></section><div class="benefits"><span>UAE delivery, all seven emirates</span><span>Prices in AED</span><span>Guest checkout available</span></div><section class="section"><div class="section-heading"><div><span class="eyebrow">A considered collection</span><h2>Find your everyday.</h2></div><a href="<?php echo esc_url(
    nisma_url("categories"),
); ?>">All collections ↗</a></div><?php echo nisma_categories(); ?></section><section class="section product-edit"><div class="section-heading"><div><span class="eyebrow">The everyday edit</span><h2>Little things. Lasting rituals.</h2></div><a href="<?php echo esc_url(
    wc_get_page_permalink("shop"),
); ?>">Shop all pieces ↗</a></div><?php echo do_shortcode(
    '[products limit="4" columns="4" orderby="menu_order" order="ASC"]',
); ?></section><section class="story"><img loading="lazy" width="1200" height="1200" src="<?php echo esc_url(
    get_template_directory_uri() . "/images/ceramic.webp",
); ?>" alt="A minimalist ceramic incense holder in soft light"><div><span class="eyebrow">The Nisma point of view</span><h2>Less rush.<br>More room.</h2><p>A favourite scarf. A page to yourself. A gift chosen with care. We believe the everyday deserves a little thought.</p><p>Nisma is a fictional lifestyle store created as a web-development portfolio concept.</p><a href="<?php echo esc_url(
    nisma_url("about"),
); ?>">Meet the concept ↗</a></div></section><section class="closing"><span class="eyebrow">Something worth giving</span><h2>A small gesture can say a lot.</h2><a class="button" href="<?php echo esc_url(
    home_url("/?product_cat=gifts"),
); ?>">Explore gifts & home ↗</a></section></main><?php get_footer(); ?>
