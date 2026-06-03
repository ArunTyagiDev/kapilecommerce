<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;
use App\Models\Product;

foreach (['shoes', 'crockery'] as $slug) {
    $root = Category::where('slug', $slug)->first();
    if (! $root) {
        echo "{$slug}: missing\n";
        continue;
    }
    $ids = [$root->id];
    $children = Category::where('parent_id', $root->id)->pluck('id')->all();
    $ids = array_merge($ids, $children);

    $products = Product::whereIn('category_id', $ids)->where('is_active', true)->get();
    echo "\n{$slug} ({$products->count()} products)\n";
    foreach ($products as $p) {
        $img = $p->primaryImage;
        $path = $img?->image_path;
        $disk = $path && file_exists(storage_path('app/public/' . $path));
        $size = $disk ? filesize(storage_path('app/public/' . $path)) : 0;
        echo "  [" . ($disk ? 'ok' : 'MISSING') . "] {$p->slug} — {$path} ({$size} bytes)\n";
    }
}
