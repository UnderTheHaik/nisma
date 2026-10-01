<?php
// Idempotent CLI seed; never resets existing inventory or orders.
if (PHP_SAPI !== "cli") {
    exit();
}
define(
    "WP_INSTALLING",
    !file_exists(__DIR__ . "/../work/nisma-runtime/admin-credentials.txt"),
);
require __DIR__ . "/../work/nisma-runtime/wordpress/wordpress/wp-load.php";
require_once ABSPATH . "wp-admin/includes/plugin.php";
require_once ABSPATH . "wp-admin/includes/upgrade.php";
if (!is_blog_installed()) {
    $password = bin2hex(random_bytes(12));
    wp_install(
        "Nisma",
        "nisma_admin",
        "hello@example.test",
        false,
        "",
        $password,
    );
    file_put_contents(
        __DIR__ . "/../work/nisma-runtime/admin-credentials.txt",
        "Username: nisma_admin\nPassword: $password\n",
    );
}
wp_installing(false);
foreach (
    ["woocommerce/woocommerce.php", "nisma-demo/nisma-demo.php"]
    as $plugin
) {
    $error = activate_plugin($plugin);
    if (is_wp_error($error)) {
        throw new RuntimeException($error->get_error_message());
    }
}
if (!class_exists("WooCommerce")) {
    require WP_PLUGIN_DIR . "/woocommerce/woocommerce.php";
    WooCommerce::instance();
}
if (!WC()->product_factory) {
    WC()->init();
}
if (!get_option("woocommerce_version")) {
    WC_Install::install();
}
WC_Post_Types::register_taxonomies();
WC_Post_Types::register_post_types();
switch_theme("nisma");
foreach (
    [
        "woocommerce_currency" => "AED",
        "woocommerce_weight_unit" => "kg",
        "woocommerce_enable_myaccount_registration" => "yes",
        "woocommerce_registration_generate_password" => "no",
        "woocommerce_default_country" => "AE",
        "woocommerce_price_num_decimals" => 2,
        "woocommerce_calc_taxes" => "no",
        "woocommerce_enable_guest_checkout" => "yes",
        "woocommerce_enable_signup_and_login_from_checkout" => "yes",
        "woocommerce_manage_stock" => "yes",
        "woocommerce_hold_stock_minutes" => 30,
        "woocommerce_ship_to_countries" => "specific",
        "woocommerce_specific_ship_to_countries" => ["AE"],
        "woocommerce_allowed_countries" => "specific",
        "woocommerce_specific_allowed_countries" => ["AE"],
        "woocommerce_coming_soon" => "no",
        "woocommerce_store_pages_only" => "no",
        "blog_public" => 0,
        "timezone_string" => "Asia/Dubai",
        "blogdescription" =>
            "Thoughtful essentials for everyday rituals. Fictional UAE portfolio store.",
    ]
    as $key => $value
) {
    update_option($key, $value);
}
function nisma_page($slug, $title, $content)
{
    $page = get_page_by_path($slug);
    $data = [
        "post_type" => "page",
        "post_status" => "publish",
        "post_name" => $slug,
        "post_title" => $title,
        "post_content" => $content,
    ];
    if ($page) {
        $data["ID"] = $page->ID;
        return wp_update_post($data);
    }
    return wp_insert_post($data);
}
$home = nisma_page("home", "Nisma — thoughtful essentials", "");
update_option("show_on_front", "page");
update_option("page_on_front", $home);
foreach (
    [
        "shop" => ["Shop the collection", ""],
        "cart" => ["Your bag", "[woocommerce_cart]"],
        "checkout" => ["Checkout", "[woocommerce_checkout]"],
        "myaccount" => ["Your account", "[woocommerce_my_account]"],
    ]
    as $slug => [$title, $content]
) {
    update_option(
        "woocommerce_" . $slug . "_page_id",
        nisma_page($slug, $title, $content),
    );
}
$pages = [
    "categories" => ["Explore our collections", "[nisma_categories]"],
    "wishlist" => [
        "Your saved pieces",
        "<p>Keep a little room for what you love. Saved pieces stay in this browser.</p>[nisma_wishlist]",
    ],
    "about" => [
        "A little more intention",
        '<p class="intro">Nisma is a fictional UAE lifestyle store, built around the small things that make an ordinary day feel considered.</p><p>Soft scarves. A fresh page. A quiet moment before the day begins. Our concept collection brings together useful objects with a calm palette and tactile materials.</p><h2>A portfolio concept</h2><p>This is an independent web-development portfolio project, not a real retailer or client. Products, materials, prices, stock and delivery policies are illustrative. Product images are free Pexels stock photography used as illustrative placeholders, not exact catalogue photographs.</p><p><a href="/?pagename=case-study">Explore the project case study →</a></p>',
    ],
    "contact" => [
        "Let’s talk",
        "<p>For this concept, customer service is represented by the form below. Messages are validated locally but never emailed or submitted to a real business.</p>[nisma_contact]",
    ],
    "faq" => [
        "Good to know",
        "<details><summary>Can I place a real order?</summary><p>No. This portfolio concept accepts local test orders only. No items are dispatched and no payment is collected.</p></details><details><summary>Where would you deliver?</summary><p>The sample store serves all seven UAE emirates. Standard delivery is estimated at 2–4 business days.</p></details><details><summary>How do I care for my scarf?</summary><p>Hand wash gently in cool water and lay flat to dry. See each product for its sample materials and care notes.</p></details><details><summary>Can I checkout without an account?</summary><p>Yes. Guest checkout is enabled. Creating an account is optional.</p></details><details><summary>How do returns work?</summary><p>The illustrative policy allows unused items in original packaging to be returned within 14 days. Read Shipping & Returns for exclusions.</p></details><details><summary>Is there a test coupon?</summary><p>Use FIRST10 for 10% off product totals of AED 100 or more. One coupon per order.</p></details>",
    ],
    "shipping-returns" => [
        "Shipping & returns",
        '<p class="intro">Sample policy for a fictional store. No real deliveries or returns take place.</p><h2>Across the UAE</h2><p>Standard: AED 20, estimated 2–4 business days. Free standard delivery from AED 250 after discounts. Express: AED 35, estimated 1–2 business days. Estimates exclude weekends and public holidays.</p><h2>A useful delivery address</h2><p>Select your emirate and include your area, building or villa, apartment where applicable, street and mobile number. Add a landmark or delivery instruction if it helps.</p><h2>Returns</h2><p>Our example policy accepts unused, unwashed items with original packaging within 14 days of delivery. Opened home-fragrance items cannot be returned unless faulty. Original delivery costs are not refunded; sample return shipping is AED 20. Contact the store before sending anything back. These policies require legal and operational review before a real launch.</p>',
    ],
    "privacy" => [
        "Privacy notice",
        "<p>This local portfolio demo stores account details, addresses, test orders and cart session cookies on this computer. Use fictional information. WooCommerce uses essential cookies to keep your cart and login working; the wishlist uses local storage in your browser.</p><p>No analytics, marketing trackers or third-party payment requests are enabled. Outbound order emails are suppressed locally. To remove test records, the project owner can delete orders and accounts in WordPress administration; clear browser storage to remove saved pieces.</p><p>This notice describes the local demo only. A real deployment needs a reviewed privacy notice, retention schedule and appropriate contact details.</p>",
    ],
    "terms" => [
        "Terms of this concept",
        "<p>Nisma is a fictional portfolio project. Browsing and placing test orders creates no purchase contract, no charge and no delivery commitment. All AED prices, discounts, stock quantities and product specifications are sample content.</p><p>Do not enter real card details or sensitive personal information. The local test gateway simulates approval or decline without contacting a payment provider. A real launch requires verified products, business identity, tax setup and reviewed consumer terms.</p>",
    ],
    "case-study" => [
        "From discovery to delivery",
        '<p class="intro">A working WordPress + WooCommerce portfolio concept for a small UAE lifestyle store.</p><h2>Customer journey</h2><p>Home → collection or search → filtered shop → product details and variation → bag → guest checkout → order confirmation. Saved pieces provide a second path back to products.</p><h2>Product information architecture</h2><p>Four collections: Scarves, Journals & stationery, Prayer essentials, and Gifts & home. Product pages separate description, materials, care and delivery. Scarves use colour variations; prayer mats use size variations. Inventory and prices live in WooCommerce.</p><h2>Mobile checkout decisions</h2><p>Guest checkout avoids a mandatory account. Single-column fields, visible labels and native inputs support phone keyboards. UAE emirate, area, building/villa, optional apartment and delivery instructions reflect local addresses. The order summary and available shipping rates stay in the checkout flow.</p><h2>Cart flow</h2><p>WooCommerce owns quantities, coupon validation, shipping calculation, totals and stock reduction. FIRST10 is a real WooCommerce coupon. Empty-cart and sold-out states offer clear recovery routes. The local gateway supports approved and declined test payments.</p><h2>Responsive and accessible</h2><p>Product grids change from two to four columns. Navigation wraps on small screens. Semantic landmarks, a skip link, visible focus rings and native form controls keep the store usable with a keyboard. Motion is limited to simple state changes.</p><h2>WooCommerce configuration</h2><p>AED; UAE-only sales and shipping; guest checkout; optional account registration; per-variation stock; standard, free and express shipping; classic checkout for custom address fields. Local SQLite is for single-user evaluation. Production needs MySQL/MariaDB, scheduled jobs, HTTPS and an explicitly configured sandbox provider.</p>',
    ],
];
foreach ($pages as $slug => [$title, $content]) {
    $id = nisma_page($slug, $title, $content);
    if ($slug === "privacy") {
        update_option("wp_page_for_privacy_policy", $id);
    }
    if ($slug === "terms") {
        update_option("woocommerce_terms_page_id", $id);
    }
}
$cats = [];
foreach (
    [
        "scarves" => "Scarves",
        "stationery" => "Journals & stationery",
        "prayer" => "Prayer essentials",
        "gifts" => "Gifts & home",
    ]
    as $slug => $name
) {
    $term = get_term_by("slug", $slug, "product_cat");
    if (!$term) {
        $term = wp_insert_term($name, "product_cat", ["slug" => $slug]);
    }
    $cats[$slug] = is_array($term) ? $term["term_id"] : $term->term_id;
}
$catalog = [
    [
        "Cloud modal scarf",
        "scarves",
        95,
        75,
        "scarf",
        "A softly draped everyday scarf with a smooth, lightweight finish.",
        "Modal blend · 180 × 70 cm",
        "Hand wash cold. Dry flat.",
        ["Sage", "Sand", "Rose"],
    ],
    [
        "Everyday linen journal",
        "stationery",
        85,
        0,
        "journal",
        "A place for plans, reflections and the thoughts worth keeping.",
        "Linen cover · 160 plain pages · A5",
        "Keep dry. Wipe cover with a soft cloth.",
        [],
    ],
    [
        "Quiet moments prayer mat",
        "prayer",
        160,
        0,
        "mat",
        "A softly padded mat in a calm, understated palette.",
        "Cotton blend · Standard 70 × 110 cm; Travel 60 × 100 cm",
        "Spot clean and air dry.",
        ["Standard", "Travel"],
    ],
    [
        "Olive ceramic incense holder",
        "gifts",
        65,
        0,
        "ceramic",
        "A sculptural little home for your incense, with a removable ash dish. Incense not included.",
        "Glazed ceramic · 12 cm diameter",
        "Wipe clean. Use on a heat-resistant surface; never leave burning incense unattended.",
        [],
    ],
    [
        "Wooden prayer beads",
        "prayer",
        45,
        0,
        "beads",
        "Natural wood beads with a simple cotton tassel.",
        "Wood · 33 beads",
        "Wipe with a dry cloth. Avoid soaking.",
        [],
    ],
    [
        "The thoughtful gift box",
        "gifts",
        220,
        0,
        "gift",
        "A modal scarf, linen journal and wooden beads, presented together.",
        "Sage scarf · A5 journal · 33 wooden beads · recyclable paper box",
        "Follow the care guidance for each included item.",
        [],
    ],
    [
        "Small notes stationery set",
        "stationery",
        40,
        0,
        "notes",
        "A compact set of note cards for everyday correspondence.",
        "20 uncoated paper cards and envelopes · A6",
        "Store flat in a dry place.",
        [],
    ],
    [
        "Rose woven scarf",
        "scarves",
        110,
        0,
        "rose",
        "Soft texture and a dusty rose tone for a considered everyday wardrobe.",
        "Viscose blend · 180 × 70 cm",
        "Hand wash separately. Cool iron.",
        [],
    ],
];
require_once ABSPATH . "wp-admin/includes/image.php";
foreach (
    $catalog
    as $i =>
        [
            $name,
            $cat,
            $price,
            $sale,
            $image,
            $desc,
            $materials,
            $care,
            $variants,
        ]
) {
    $sku = "NISMA-" . ($i + 1);
    $existing = wc_get_product_id_by_sku($sku);
    $p = $existing
        ? wc_get_product($existing)
        : ($variants
            ? new WC_Product_Variable()
            : new WC_Product_Simple());
    $p->set_name($name);
    $p->set_sku($sku);
    $p->set_status("publish");
    $p->set_category_ids([$cats[$cat]]);
    $p->set_menu_order($i);
    $p->set_short_description($desc);
    $p->set_description(
        "<p>" .
            esc_html($desc) .
            "</p><p>Illustrative product specification for the Nisma portfolio concept.</p>",
    );
    $p->set_weight("0.3");
    $p->update_meta_data("_nisma_materials", $materials);
    $p->update_meta_data("_nisma_care", $care);
    if ($variants) {
        $a = new WC_Product_Attribute();
        $a->set_name($cat === "scarves" ? "Colour" : "Size");
        $a->set_options($variants);
        $a->set_visible(true);
        $a->set_variation(true);
        $p->set_attributes([$a]);
    } else {
        $p->set_regular_price($price);
        if (!$existing) {
            $p->set_manage_stock(true);
            $p->set_stock_quantity($i === 6 ? 0 : 12);
        }
    }
    $id = $p->save();
    if (
        !$p->get_image_id() &&
        is_file(__DIR__ . "/theme/images/" . $image . ".webp")
    ) {
        $upload = wp_upload_bits(
            $image . ".webp",
            null,
            file_get_contents(__DIR__ . "/theme/images/" . $image . ".webp"),
        );
        $aid = wp_insert_attachment(
            [
                "post_mime_type" => "image/webp",
                "post_title" => $name,
                "post_status" => "inherit",
            ],
            $upload["file"],
            $id,
        );
        wp_update_attachment_metadata(
            $aid,
            wp_generate_attachment_metadata($aid, $upload["file"]),
        );
        update_post_meta(
            $aid,
            "_wp_attachment_image_alt",
            $name . " — illustrative stock photograph",
        );
        $p->set_image_id($aid);
        $p->save();
    }
    if ($variants && !$existing) {
        foreach ($variants as $n => $value) {
            $v = new WC_Product_Variation();
            $v->set_parent_id($id);
            $v->set_attributes([
                sanitize_title(
                    $cat === "scarves" ? "Colour" : "Size",
                ) => $value,
            ]);
            $v->set_regular_price($price);
            if ($sale) {
                $v->set_sale_price($sale);
            }
            $v->set_manage_stock(true);
            $v->set_stock_quantity($n === 2 ? 0 : 8);
            $v->set_sku($sku . "-" . $n);
            $v->save();
        }
    }
    if ($variants) {
        WC_Product_Variable::sync($id);
    }
    wc_delete_product_transients($id);
}
if (!get_option("nisma_shipping_seeded")) {
    $zone = new WC_Shipping_Zone();
    $zone->set_zone_name("United Arab Emirates");
    $zone->add_location("AE", "country");
    $zone->save();
    foreach (
        [
            ["flat_rate", "Standard · 2–4 business days", "20"],
            ["flat_rate", "Express · 1–2 business days", "35"],
            ["free_shipping", "Free standard · 2–4 business days", "250"],
        ]
        as [$method, $title, $cost]
    ) {
        $id = $zone->add_shipping_method($method);
        $settings = [
            "enabled" => "yes",
            "title" => $title,
            "tax_status" => "none",
            "cost" => $cost,
        ];
        if ($method === "free_shipping") {
            $settings += [
                "requires" => "min_amount",
                "min_amount" => $cost,
                "ignore_discounts" => "no",
            ];
        }
        update_option(
            "woocommerce_" . $method . "_" . $id . "_settings",
            $settings,
        );
    }
    update_option("nisma_shipping_seeded", true);
}
if (!wc_get_coupon_id_by_code("FIRST10")) {
    $c = new WC_Coupon();
    $c->set_code("FIRST10");
    $c->set_discount_type("percent");
    $c->set_amount(10);
    $c->set_minimum_amount(100);
    $c->set_individual_use(true);
    $c->save();
}
flush_rewrite_rules();
echo "Nisma seeded.\n";
