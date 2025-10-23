<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/vendor/orchestra/testbench-core/laravel/bootstrap/app.php';
$app->register(VinkiusLabs\Trilean\TernaryLogicServiceProvider::class);
$app->boot();

$output = Illuminate\Support\Facades\Blade::render('@ternary(true)allowed@endternary');

echo $output, PHP_EOL;
