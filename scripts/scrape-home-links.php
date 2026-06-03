<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$html = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->timeout(60)->get('https://omgs.in/')->body();

preg_match_all('#href=["\']([^"\']*(?:customise|product-category|/custom/)[^"\']*)["\']#i', $html, $m);
$links = array_unique($m[1] ?? []);
sort($links);
foreach ($links as $link) {
    echo $link . "\n";
}
