<?php
declare(strict_types=1);

$router->get('/', [$publicController, 'home']);
$router->get('/about', [$publicController, 'about']);
$router->get('/contact', [$publicController, 'contact']);
$router->get('/how-to-buy', [$publicController, 'howToBuy']);
$router->get('/como-comprar', [$publicController, 'howToBuy']);
$router->get('/categories', [$publicController, 'categories']);
$router->get('/category/{slug}', [$publicController, 'categoryShow']);
$router->get('/product/{code}', [$publicController, 'productShow']);

$router->get('/admin/login', [$authController, 'showLogin']);
$router->post('/admin/login', [$authController, 'login']);
$router->get('/admin', [$authController, 'dashboard']);
$router->post('/admin/logout', [$authController, 'logout']);
