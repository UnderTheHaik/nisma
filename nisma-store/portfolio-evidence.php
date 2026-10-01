<?php
if (PHP_SAPI !== 'cli') exit;
if (!defined('ABSPATH')) require_once __DIR__.'/../work/nisma-runtime/wordpress/wordpress/wp-load.php';
$page=get_page_by_path('case-study');
if (!$page) exit;
$base=get_template_directory_uri().'/images/portfolio/';
$evidence='<section id="checkout-evidence"><h2>The local WooCommerce interface</h2><p>Actual screenshots captured on 1 October 2026 in the local test store. Fictional customer details only; no payment was submitted. The public GitHub Pages storefront is a static preview.</p><div class="checkout-captures">';
foreach (['cart'=>'Bag: quantities, shipping choices and server-calculated totals.','checkout'=>'Guest checkout: UAE address fields, order summary and approved/declined local payment simulator.'] as $file=>$caption) {
  $url=esc_url($base.$file.'.webp');
  $evidence.='<figure><a href="'.$url.'" aria-label="Open full checkout screenshot"><img loading="lazy" src="'.$url.'" alt="'.esc_attr($caption).'"></a><figcaption>'.esc_html($caption).'</figcaption></figure>';
}
$evidence.='</div><p>Shown example: AED 85 journal + AED 20 standard shipping = AED 105. The local demo also supports coupon validation, variable inventory, declined payment and approved retry. Full store configuration is documented in the project README.</p></section>';
$content=preg_replace('/<section id="checkout-evidence">[\s\S]*?<\/section>/','',$page->post_content);
wp_update_post(['ID'=>$page->ID,'post_content'=>$content.$evidence]);
echo "Portfolio checkout evidence updated.\n";
