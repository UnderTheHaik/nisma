<?php
if (PHP_SAPI !== "cli") {
    exit();
}
require __DIR__ . "/../work/nisma-runtime/wordpress/wordpress/wp-load.php";
function check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
    echo "PASS: $message\n";
}
check(get_stylesheet() === "nisma", "Nisma theme active");
check(get_woocommerce_currency() === "AED", "AED currency");
check(get_option("woocommerce_default_country") === "AE", "UAE store");
$products = wc_get_products(["limit" => -1, "status" => "publish"]);
check(count($products) === 8, "Eight published products");
foreach ($products as $p) {
    check((bool) $p->get_image_id(), $p->get_name() . " has image");
    check(
        (bool) $p->get_meta("_nisma_materials"),
        $p->get_name() . " has materials",
    );
}
$p = wc_get_product(wc_get_product_id_by_sku("NISMA-1"));
check(
    $p->is_type("variable") && count($p->get_children()) === 3,
    "Scarf colour variants",
);
$v = wc_get_product($p->get_children()[0]);
check($v->get_price() == 75 && $v->is_on_sale(), "Variation sale price");
$out = wc_get_product(wc_get_product_id_by_sku("NISMA-1-2"));
check(!$out->is_in_stock(), "Unavailable Rose variant");
foreach (
    [
        "categories",
        "cart",
        "checkout",
        "myaccount",
        "about",
        "contact",
        "faq",
        "shipping-returns",
        "privacy",
        "terms",
        "case-study",
        "photo-credits",
        "wishlist",
    ]
    as $slug
) {
    check((bool) get_page_by_path($slug), "Page " . $slug);
}
check((new WC_Coupon("FIRST10"))->get_amount() == 10, "FIRST10 coupon");
$gateways = WC()->payment_gateways()->payment_gateways();
check(isset($gateways["nisma_demo"]), "Local test gateway registered");
check(
    get_option("woocommerce_enable_guest_checkout") === "yes",
    "Guest checkout enabled",
);
$fields = apply_filters("woocommerce_checkout_fields", [
    "billing" => WC()->countries->get_address_fields("AE", "billing_"),
    "shipping" => WC()->countries->get_address_fields("AE", "shipping_"),
    "order" => ["order_comments" => []],
]);
check(
    $fields["billing"]["billing_state"]["label"] === "Emirate",
    "UAE Emirate field",
);
check(
    !isset($fields["billing"]["billing_postcode"]),
    "No UAE postcode requirement",
);
$zones = WC_Shipping_Zones::get_zones();
$methods = [];
foreach ($zones as $zone) {
    foreach ($zone["shipping_methods"] as $method) {
        $methods[] = $method;
    }
}
check(count($methods) === 3, "Three shipping options");
// A real cart calculation exercises discounts and totals without creating an order.
WC()->initialize_session();
WC()->initialize_cart();
WC()->customer->set_billing_country("AE");
WC()->customer->set_shipping_country("AE");
WC()->customer->set_shipping_state("DU");
WC()->customer->set_shipping_city("Demo area");
WC()->cart->empty_cart();
$journal = wc_get_product_id_by_sku("NISMA-2");
check((bool) WC()->cart->add_to_cart($journal, 2), "Journal added to cart");
check(WC()->cart->apply_coupon("FIRST10"), "Coupon accepted above minimum");
WC()->cart->calculate_totals();
check(
    abs(WC()->cart->get_discount_total() - 17) < 0.01,
    "10% discount computed server-side",
);
WC()->cart->empty_cart();

// Confirm free delivery depends on the discounted total, not the subtotal.
WC()->cart->add_to_cart($journal, 3);
WC()->cart->apply_coupon("FIRST10");
WC()->cart->calculate_totals();
$packages = WC()->shipping()->get_packages();
$hasFree = static function ($packages) {
    foreach ($packages as $package) {
        foreach ($package["rates"] as $rate) {
            if ($rate->method_id === "free_shipping") {
                return true;
            }
        };
    }
    return false;
};
check(
    !$hasFree($packages),
    "AED 255 before discount does not receive free shipping after FIRST10",
);
WC()->cart->add_to_cart($journal, 1);
WC()->cart->calculate_totals();
check(
    $hasFree(WC()->shipping()->get_packages()),
    "AED 306 after discount receives free shipping",
);
WC()->cart->empty_cart();
if (isset($argv[1])) {
    $order = wc_get_order(absint($argv[1]));
    check($order && $order->is_paid(), "Specified test order is paid");
    check($order->get_meta("_order_stock_reduced"), "Test order reduced stock");
    check(
        (bool) $order->get_meta("_nisma_billing_street"),
        "Street saved on order",
    );
}

echo "All checks passed. No real payment submitted.\n";
