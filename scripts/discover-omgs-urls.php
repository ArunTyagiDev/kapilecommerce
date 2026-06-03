<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$omgs = app(\App\Services\OmgsCatalogService::class);

$urls = [
    'https://omgs.in/product-category/acrylic-photo-stand',
    'https://omgs.in/customise/collage-acrylic-wall-photo',
    'https://omgs.in/product-category/luggage-tags',
    'https://omgs.in/product-category/photo-albums',
    'https://omgs.in/product/customised-premium-framed-acrylic-photo',
    'https://omgs.in/customise/clear-acrylic-photo',
    'https://omgs.in/customise/acrylic-desk',
];

foreach ($urls as $url) {
    $n = count($omgs->scrapeProductNames($url));
    echo "$n $url\n";
}
