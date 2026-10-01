<?php
/** Theme behaviour; store rules belong in the demo plugin. */
defined("ABSPATH") || exit();
add_action("after_setup_theme", function () {
    add_theme_support("title-tag");
    add_theme_support("post-thumbnails");
    add_theme_support("woocommerce");
    add_theme_support("wc-product-gallery-slider");
});
add_action("wp_enqueue_scripts", function () {
    wp_enqueue_style(
        "nisma",
        get_stylesheet_uri(),
        [],
        filemtime(get_stylesheet_directory() . "/style.css"),
    );
    wp_enqueue_script(
        "nisma",
        get_template_directory_uri() . "/store.js",
        [],
        filemtime(get_stylesheet_directory() . "/store.js"),
        true,
    );
});
remove_action("woocommerce_sidebar", "woocommerce_get_sidebar", 10);
add_filter("loop_shop_columns", fn() => 4);
add_filter(
    "woocommerce_currency_symbol",
    fn($s, $c) => $c === "AED" ? "AED " : $s,
    10,
    2,
);
function nisma_url($slug)
{
    return home_url("/?pagename=" . $slug);
}
function nisma_categories()
{
    ob_start(); ?><div class="category-grid"><?php foreach (
    [
        ["scarves", "Scarves", "scarf", "Soft layers, every day"],
        [
            "stationery",
            "Journals & stationery",
            "journal",
            "Make space for your thoughts",
        ],
        ["prayer", "Prayer essentials", "mat", "For moments of reflection"],
        [
            "gifts",
            "Gifts & home",
            "gift",
            "Small gestures, thoughtfully chosen",
        ],
    ]
    as [$slug, $name, $image, $line]
) { ?><a class="category" href="<?php echo esc_url(
    home_url("/?product_cat=" . $slug),
); ?>"><img loading="lazy" width="600" height="750" src="<?php echo esc_url(
    get_template_directory_uri() . "/images/" . $image . ".webp",
); ?>" alt="<?php echo esc_attr(
    $name . " — illustrative stock photo",
); ?>"><h3><?php echo esc_html(
    $name,
); ?> <span aria-hidden="true">↗</span></h3><p><?php echo esc_html(
     $line,
 ); ?></p></a><?php } ?></div><?php return ob_get_clean();
}
add_shortcode("nisma_categories", "nisma_categories");
add_action("wp_head", function () {
    $description = is_product()
        ? wp_strip_all_tags(
            wc_get_product(get_the_ID())->get_short_description(),
        )
        : "Nisma: thoughtful scarves, stationery, prayer essentials and gifts. A fictional UAE WooCommerce portfolio concept.";
    $image = is_product()
        ? wp_get_attachment_image_url(
            wc_get_product(get_the_ID())->get_image_id(),
            "large",
        )
        : get_template_directory_uri() . "/images/scarf.webp";
    ?><meta name="description" content="<?php echo esc_attr($description); ?>"><meta property="og:title" content="<?php echo esc_attr(wp_get_document_title()); ?>"><meta property="og:description" content="<?php echo esc_attr($description); ?>"><meta property="og:type" content="<?php echo is_product() ? "product" : "website"; ?>"><meta property="og:image" content="<?php echo esc_url($image); ?>"><meta property="og:site_name" content="Nisma · Portfolio concept"><meta property="og:url" content="<?php echo esc_url(is_front_page() ? home_url("/") : get_permalink()); ?>"><meta name="twitter:card" content="summary_large_image"><link rel="icon" type="image/svg+xml" href="<?php echo esc_url(get_template_directory_uri() . "/favicon.svg"); ?>"><?php
});
add_action(
    "wp_loaded",
    function () {
        foreach (["min_price", "max_price"] as $key) {
            if (isset($_GET[$key]) && trim((string) $_GET[$key]) === "") {
                unset($_GET[$key]);
            }
        }
    },
    1,
);
function nisma_filters()
{
    ?><details class="shop-filters" open><summary>Filter the collection</summary><form method="get" action="<?php echo esc_url(
    wc_get_page_permalink("shop"),
); ?>"><input type="hidden" name="post_type" value="product"><label>Search<input type="search" name="s" placeholder="Find a piece…" value="<?php echo esc_attr(
    get_search_query(),
); ?>"></label><label>Collection<select name="collection"><option value="">All collections</option><?php foreach (
    [
        "scarves" => "Scarves",
        "stationery" => "Stationery",
        "prayer" => "Prayer essentials",
        "gifts" => "Gifts & home",
    ]
    as $slug => $title
) { ?><option value="<?php echo esc_attr($slug); ?>" <?php selected(
    $_GET["collection"] ?? "",
    $slug,
); ?>><?php echo esc_html(
    $title,
); ?></option><?php } ?></select></label><label>Maximum AED<input type="number" name="max_price" min="0" value="<?php echo esc_attr(
    $_GET["max_price"] ?? "",
); ?>" placeholder="Any price"></label><label>Colour<select name="colour"><option value="">Any colour</option><?php foreach (
    ["Sage", "Sand", "Rose"]
    as $colour
) { ?><option <?php selected(
    $_GET["colour"] ?? "",
    $colour,
); ?>><?php echo esc_html(
    $colour,
); ?></option><?php } ?></select></label><label>Size<select name="size"><option value="">Any size</option><?php foreach (
    ["Standard", "Travel"]
    as $size
) { ?><option <?php selected(
    $_GET["size"] ?? "",
    $size,
); ?>><?php echo esc_html(
    $size,
); ?></option><?php } ?></select></label><label class="check"><input type="checkbox" name="available" value="1" <?php checked(
    isset($_GET["available"]),
); ?>> In stock only</label><button class="button">Apply</button><a href="<?php echo esc_url(
    wc_get_page_permalink("shop"),
); ?>">Reset</a></form></details><?php
}
add_action("woocommerce_before_shop_loop", "nisma_filters", 15);
add_action("woocommerce_no_products_found", "nisma_filters", 5);
add_action("woocommerce_product_query", function ($q) {
    $tax = (array) $q->get("tax_query");
    if (!empty($_GET["collection"])) {
        $tax[] = [
            "taxonomy" => "product_cat",
            "field" => "slug",
            "terms" => sanitize_title(wp_unslash($_GET["collection"])),
        ];
    }
    $q->set("tax_query", $tax);
    if (isset($_GET["available"])) {
        $meta = (array) $q->get("meta_query");
        $meta[] = ["key" => "_stock_status", "value" => "instock"];
        $q->set("meta_query", $meta);
    }
    $variationMeta = [];
    foreach (["colour", "size"] as $attribute) {
        if (!empty($_GET[$attribute])) {
            $variationMeta[] = [
                "key" => "attribute_" . $attribute,
                "value" => sanitize_text_field(wp_unslash($_GET[$attribute])),
            ];
        }
    }
    if ($variationMeta) {
        if (isset($_GET["available"])) {
            $variationMeta[] = ["key" => "_stock_status", "value" => "instock"];
        }
        $v = get_posts([
            "post_type" => "product_variation",
            "post_status" => "publish",
            "numberposts" => -1,
            "meta_query" => $variationMeta,
        ]);
        $parents = array_unique(array_map(fn($p) => (int) $p->post_parent, $v));
        $q->set("post__in", $parents ?: [0]);
    }
});
function nisma_save_button()
{
    global $product; ?><button type="button" class="save-piece" data-save="<?php echo absint(
    $product->get_id(),
); ?>" aria-pressed="false" aria-label="<?php echo esc_attr(
    "Save " . $product->get_name(),
); ?>">♡ <span>Save</span></button><?php
}
add_action("woocommerce_after_shop_loop_item", "nisma_save_button", 20);
add_action("woocommerce_single_product_summary", "nisma_save_button", 35);
add_shortcode("nisma_wishlist", function () {
    $products = wc_get_products(["limit" => -1, "status" => "publish"]);
    $data = [];
    foreach ($products as $p) {
        $data[] = [
            "id" => $p->get_id(),
            "name" => $p->get_name(),
            "url" => $p->get_permalink(),
            "image" => wp_get_attachment_image_url(
                $p->get_image_id(),
                "medium",
            ),
            "price" => wp_strip_all_tags($p->get_price_html()),
        ];
    }
    return '<div id="saved-pieces" class="saved-grid"></div><script type="application/json" id="saved-catalog">' .
        wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP) .
        "</script><noscript>Enable JavaScript to view locally saved pieces.</noscript>";
});
add_filter("woocommerce_product_tabs", function ($tabs) {
    foreach (
        [
            "materials" => "Materials",
            "care" => "Care",
            "delivery" => "Delivery & returns",
        ]
        as $key => $label
    ) {
        $tabs[$key] = [
            "title" => $label,
            "priority" =>
                $key === "materials" ? 15 : ($key === "care" ? 16 : 17),
            "callback" => "nisma_product_tab",
        ];
    }
    unset($tabs["reviews"]);
    return $tabs;
});
function nisma_product_tab($key)
{
    global $product;
    echo "<h2>" .
        esc_html(
            [
                "materials" => "Materials",
                "care" => "Care",
                "delivery" => "Delivery & returns",
            ][$key],
        ) .
        "</h2>";
    if ($key === "delivery") {
        echo '<p>UAE standard delivery: 2–4 business days, AED 20. Free from AED 250 after discounts. Express: 1–2 business days, AED 35.</p><p>Sample 14-day returns for unused items. Opened fragrance items excluded unless faulty. No real deliveries in this concept.</p><a href="' .
            esc_url(nisma_url("shipping-returns")) .
            '">Read the sample policy →</a>';
    } else {
        echo "<p>" . esc_html($product->get_meta("_nisma_" . $key)) . "</p>";
    }
}
add_action(
    "woocommerce_single_product_summary",
    function () {
        echo '<p class="product-note">Illustrative stock photography · Sample product specifications</p>';
    },
    22,
);
add_action(
    "woocommerce_before_checkout_form",
    function () {
        echo '<div class="demo-label">Test checkout only. Use fictional details. No money is collected and no items are dispatched.</div>';
    },
    5,
);
add_filter("woocommerce_checkout_fields", function ($fields) {
    unset(
        $fields["billing"]["billing_postcode"],
        $fields["shipping"]["shipping_postcode"],
    );
    foreach (["billing", "shipping"] as $type) {
        $fields[$type][$type . "_city"]["label"] = "Area";
        $fields[$type][$type . "_city"]["placeholder"] = "e.g. Al Barsha";
        $fields[$type][$type . "_address_1"]["label"] =
            "Building / villa and street";
        $fields[$type][$type . "_address_1"]["placeholder"] =
            "Building or villa name, street";
        $fields[$type][$type . "_address_2"]["label"] =
            "Apartment / floor (optional)";
        $fields[$type][$type . "_address_2"]["placeholder"] =
            "Apartment or floor";
        $fields[$type][$type . "_state"]["label"] = "Emirate";
    }
    $fields["order"]["order_comments"]["label"] =
        "Delivery instructions (optional)";
    $fields["order"]["order_comments"]["placeholder"] =
        "Landmark or a helpful delivery note";
    return $fields;
});
// Hide only paid standard when free standard applies; preserve express as an option.
add_filter(
    "woocommerce_package_rates",
    function ($rates) {
        $free = false;
        foreach ($rates as $r) {
            if ($r->method_id === "free_shipping") {
                $free = true;
            }
        }
        if ($free) {
            foreach ($rates as $key => $r) {
                if (
                    $r->method_id === "flat_rate" &&
                    str_starts_with($r->label, "Standard")
                ) {
                    unset($rates[$key]);
                }
            }
        }
        return $rates;
    },
    100,
);
add_shortcode("nisma_contact", function () {
    $notice = "";
    if (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["nisma_contact"])
    ) {
        if (
            !wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST["_wpnonce"] ?? "")),
                "nisma_contact",
            )
        ) {
            $notice = "The form expired. Reload and try again.";
        } elseif (
            !is_email(sanitize_email(wp_unslash($_POST["email"] ?? ""))) ||
            !trim(sanitize_text_field(wp_unslash($_POST["name"] ?? ""))) ||
            !trim(sanitize_textarea_field(wp_unslash($_POST["message"] ?? "")))
        ) {
            $notice = "Please provide your name, a valid email and a message.";
        } else {
            $notice =
                "Your demo message was validated. Nothing was sent or stored.";
        }
    }
    ob_start();
    ?><form method="post" class="contact-form"><p role="status"><?php echo esc_html($notice); ?></p><?php wp_nonce_field("nisma_contact"); ?><input type="hidden" name="nisma_contact" value="1"><label>Name<input required name="name" autocomplete="name"></label><label>Email<input required type="email" name="email" autocomplete="email"></label><label>Message<textarea required name="message" rows="5"></textarea></label><button class="button">Test message</button></form><?php return ob_get_clean();
});
// Keep a one-result search on the listing so filters and sorting remain visible.
add_filter("woocommerce_redirect_single_search_result", "__return_false");
add_action(
    "woocommerce_after_shop_loop_item_title",
    function () {
        global $product;
        if (!$product->is_in_stock()) {
            echo '<p class="stock out-of-stock">Currently unavailable</p>';
        }
    },
    12,
);
// UAE locale must also be configured: WooCommerce applies it after checkout fields.
add_filter("woocommerce_states", function ($states) {
    $states["AE"] = [
        "AZ" => "Abu Dhabi",
        "DU" => "Dubai",
        "SH" => "Sharjah",
        "AJ" => "Ajman",
        "UQ" => "Umm Al Quwain",
        "RK" => "Ras Al Khaimah",
        "FU" => "Fujairah",
    ];
    return $states;
});
add_filter("woocommerce_get_country_locale", function ($locale) {
    $locale["AE"] = [
        "state" => [
            "label" => "Emirate",
            "required" => true,
            "hidden" => false,
        ],
        "city" => ["label" => "Area", "required" => true],
        "address_1" => [
            "label" => "Building / villa",
            "placeholder" => "Building or villa name / number",
        ],
        "address_2" => [
            "label" => "Apartment / floor",
            "placeholder" => "Apartment or floor",
        ],
        "postcode" => ["required" => false, "hidden" => true],
    ];
    return $locale;
});
add_filter(
    "woocommerce_checkout_fields",
    function ($fields) {
        foreach (["billing", "shipping"] as $type) {
            $fields[$type][$type . "_street"] = [
                "type" => "text",
                "label" => "Street",
                "required" => true,
                "class" => ["form-row-wide"],
                "priority" => 65,
                "placeholder" => "Street name or number",
            ];
            $fields[$type][$type . "_address_1"]["label"] = "Building / villa";
            $fields[$type][$type . "_address_2"]["label"] = "Apartment / floor";
        }
        $fields["order"]["order_comments"]["label"] = "Delivery instructions";
        return $fields;
    },
    30,
);
add_action(
    "woocommerce_checkout_create_order",
    function ($order, $data) {
        foreach (["billing", "shipping"] as $type) {
            if (!empty($data[$type . "_street"])) {
                $order->update_meta_data(
                    "_nisma_" . $type . "_street",
                    sanitize_text_field($data[$type . "_street"]),
                );
            }
        }
    },
    10,
    2,
);
add_action("woocommerce_order_details_after_order_table", function ($order) {
    $street = $order->get_meta("_nisma_billing_street");
    if ($street) {
        echo "<p><strong>Street:</strong> " . esc_html($street) . "</p>";
    }
});
add_action("woocommerce_admin_order_data_after_billing_address", function (
    $order,
) {
    echo "<p><strong>Street:</strong> " .
        esc_html($order->get_meta("_nisma_billing_street")) .
        "</p>";
});

add_action(
    "woocommerce_checkout_create_order",
    function ($order, $data) {
        if (
            empty($data["ship_to_different_address"]) &&
            !empty($data["billing_street"])
        ) {
            $order->update_meta_data(
                "_nisma_shipping_street",
                sanitize_text_field($data["billing_street"]),
            );
        }
    },
    20,
    2,
);
add_action("woocommerce_admin_order_data_after_shipping_address", function (
    $order,
) {
    echo "<p><strong>Street:</strong> " .
        esc_html($order->get_meta("_nisma_shipping_street")) .
        "</p>";
});
// Explicit disabled state improves keyboard semantics of native WooCommerce variations.
add_action("wp_enqueue_scripts", function () {
    if (is_product()) {
        wp_enqueue_script(
            "nisma-variations",
            get_template_directory_uri() . "/variations.js",
            ["jquery", "wc-add-to-cart-variation"],
            filemtime(get_stylesheet_directory() . "/variations.js"),
            true,
        );
    }
});
// Prefer free standard when it first becomes available; retain an explicit express choice.
add_filter(
    "woocommerce_shipping_chosen_method",
    function ($default, $rates, $chosen) {
        if (
            isset($rates[$chosen]) &&
            str_starts_with($rates[$chosen]->label, "Express")
        ) {
            return $chosen;
        }
        foreach ($rates as $id => $rate) {
            if ($rate->method_id === "free_shipping") {
                return $id;
            }
        }
        return $default;
    },
    10,
    3,
);
