<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://customizeprint.in/product-category/school-name-slip-sticker/';
$r = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->timeout(60)->get($url);
$html = $r->body();
echo "status {$r->status()} len " . strlen($html) . "\n";

file_put_contents(__DIR__ . '/../storage/app/cp-school.html', substr($html, 0, 200000));

if (preg_match_all('#href=["\'](https://customizeprint\.in/product/[^"\']+)["\']#i', $html, $links)) {
    $urls = array_unique($links[1]);
    echo "product urls: " . count($urls) . "\n";
    foreach (array_slice($urls, 0, 5) as $u) {
        echo "  $u\n";
    }
}

if (preg_match_all('#<h2[^>]*class="[^"]*woocommerce-loop-product__title[^"]*"[^>]*>\s*<a[^>]+href=["\']([^"\']+)["\'][^>]*>([^<]+)</a>#i', $html, $m, PREG_SET_ORDER)) {
    echo "h2 products: " . count($m) . "\n";
}

if (preg_match_all('#<a[^>]+href=["\']([^"\']+/product/[^"\']+)["\'][^>]*>\s*<img[^>]+src=["\']([^"\']+)["\']#is', $html, $cards, PREG_SET_ORDER)) {
    echo "card img pairs: " . count($cards) . "\n";
}

if (preg_match_all('#<img[^>]+src=["\']([^"\']+)["\'][^>]*class="[^"]*attachment-woocommerce_thumbnail#i', $html, $imgs)) {
    echo "thumbs: " . count($imgs[1]) . "\n";
}

// single product page
$testUrl = $m[0][1] ?? null;
if ($testUrl) {
    $p = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($testUrl)->body();
    if (preg_match('#<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']#i', $p, $og)) {
        echo "og:image: {$og[1]}\n";
    }
    if (preg_match('#woocommerce-Price-amount[^>]*>.*?(\d+)#', $p, $pr)) {
        echo "price hint found\n";
    }
}
