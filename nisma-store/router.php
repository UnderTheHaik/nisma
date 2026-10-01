<?php
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
if (
    $path !== "/" &&
    is_file(__DIR__ . "/../work/nisma-runtime/wordpress/wordpress" . $path)
) {
    return false;
}
require __DIR__ . "/../work/nisma-runtime/wordpress/wordpress/index.php";
