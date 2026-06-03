<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$omgs = app(\App\Services\OmgsCatalogService::class);

$urls = [
    'https://omgs.in/customise/framed-acrylic-photo',
    'https://omgs.in/product-category/personalised-keychains',
    'https://omgs.in/product-category/acrylic-nameplate',
    'https://omgs.in/product-category/acrylic-desk-photo',
    'https://omgs.in/product-category/acrylic-cutout',
];

foreach ($urls as $url) {
    $items = $omgs->scrapeProductNames($url);
    echo "scrape count: " . count($items) . " for $url\n";
    if (count($items) > 0) {
        echo "  first: " . ($items[0]['name'] ?? $items[0]['slug']) . "\n";
    }
    $r = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->timeout(60)->get($url);
    $h = $r->body();
    echo "\n=== $url status {$r->status()} len " . strlen($h) . " ===\n";
    preg_match_all('#/custom/[a-z0-9\-]+#i', $h, $m);
    echo 'custom links: ' . count(array_unique($m[0] ?? [])) . "\n";
    preg_match_all('#<li[^>]*>\s*<a[^>]+href=["\'](?:https://omgs\.in)?(/custom/[^"\']+)["\']#i', $h, $m2);
    echo 'li custom links: ' . count($m2[1] ?? []) . "\n";
    echo 'has __NEXT_DATA__: ' . (str_contains($h, '__NEXT_DATA__') ? 'yes' : 'no') . "\n";
    if (preg_match('#<script id="__NEXT_DATA__"[^>]*>(.+?)</script>#s', $h, $nd)) {
        $json = json_decode($nd[1], true);
        echo 'NEXT keys: ' . implode(', ', array_keys($json ?? [])) . "\n";
    }
    file_put_contents(__DIR__ . '/../storage/app/probe-' . basename(parse_url($url, PHP_URL_PATH)) . '.html', substr($h, 0, 80000));
}
