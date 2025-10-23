<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/vendor/orchestra/testbench-core/laravel/bootstrap/app.php';
$app->register(VinkiusLabs\Trilean\TernaryLogicServiceProvider::class);
$app->boot();

$compiled = Illuminate\Support\Facades\Blade::compileString('@ternary(true) allowed @endternary');

echo $compiled, PHP_EOL;
