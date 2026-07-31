<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
file_put_contents('items.json', json_encode(App\Models\GarmentItem::all(['id', 'name', 'image_path'])->toArray(), JSON_PRETTY_PRINT));
