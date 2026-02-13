#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

$router = new Atlas\Router(new Atlas\Config(['modules_path' => ['/tmp']]));

$router->get('/users', 'Handler', 'user_list');

echo "Generated URL: " . $router->url('user_list') . "\n";
echo "Expected URL: /users\n";