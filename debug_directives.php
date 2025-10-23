<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/vendor/orchestra/testbench-core/laravel/bootstrap/app.php';
$app->register(VinkiusLabs\Trilean\TernaryLogicServiceProvider::class);
$app->boot();

$compiler = $app->make('blade.compiler');
$directives = $compiler->getCustomDirectives();

var_export(array_keys($directives));
