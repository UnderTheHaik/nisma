<?php
if (PHP_SAPI !== "cli") {
    exit();
}
require __DIR__ . "/../work/nisma-runtime/wordpress/wordpress/wp-load.php";
$credits = json_decode(file_get_contents(__DIR__ . "/images.json"), true);
$html =
    "<p>Free Pexels stock photographs are used as illustrative placeholders. They do not depict exact manufactured Nisma products. No generated pictures are used.</p><ul>";
foreach ($credits as $c) {
    $html .=
        '<li><a href="' .
        esc_url($c["source"]) .
        '">' .
        esc_html($c["title"]) .
        '</a> · <a href="' .
        esc_url($c["licenseUrl"]) .
        '">Pexels License</a></li>';
}
$html .= "</ul>";
$page = get_page_by_path("photo-credits");
$data = [
    "post_type" => "page",
    "post_status" => "publish",
    "post_name" => "photo-credits",
    "post_title" => "Photo credits",
    "post_content" => $html,
];
if ($page) {
    $data["ID"] = $page->ID;
}
wp_insert_post($data);
// Second illustrative image in each scarf gallery; product specifications are samples.
$a = wc_get_product(wc_get_product_id_by_sku("NISMA-1"));
$b = wc_get_product(wc_get_product_id_by_sku("NISMA-8"));
if ($a && $b) {
    $a->set_gallery_image_ids([$b->get_image_id()]);
    $a->save();
    $b->set_gallery_image_ids([$a->get_image_id()]);
    $b->save();
}
// Refresh optimized source files and responsive sizes on setup reruns.
require_once ABSPATH . "wp-admin/includes/image.php";
foreach ($credits as $credit) {
    $name = $credit["name"];
    $attachments = get_posts([
        "post_type" => "attachment",
        "post_status" => "inherit",
        "numberposts" => -1,
    ]);
    foreach ($attachments as $attachment) {
        $file = get_attached_file($attachment->ID);
        if (basename($file) === $name . ".webp") {
            copy(__DIR__ . "/theme/images/" . $name . ".webp", $file);
            wp_update_attachment_metadata(
                $attachment->ID,
                wp_generate_attachment_metadata($attachment->ID, $file),
            );
        }
    }
}
