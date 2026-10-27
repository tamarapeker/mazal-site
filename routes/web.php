<?php
declare(strict_types=1);

$router->get('/', [$publicController, 'home']);
$router->get('/about', [$publicController, 'about']);
$router->get('/contact', [$contactController, 'show']);
$router->post('/contact', [$contactController, 'send']);
$router->get('/how-to-buy', [$publicController, 'howToBuy']);
$router->get('/como-comprar', [$publicController, 'howToBuy']);
$router->get('/categories', [$publicController, 'categories']);
$router->get('/category/{slug}', [$publicController, 'categoryShow']);
$router->get('/product/{code}', [$publicController, 'productShow']);

$router->get('/admin/login', [$authController, 'showLogin']);
$router->post('/admin/login', [$authController, 'login']);
$router->get('/admin', [$authController, 'dashboard']);
$router->get('/admin/products', [$adminProductController, 'index']);
$router->get('/admin/products/create', [$adminProductController, 'create']);
$router->post('/admin/products', [$adminProductController, 'store']);
$router->get('/admin/products/{id}/edit', [$adminProductController, 'edit']);
$router->post('/admin/products/{id}/update', [$adminProductController, 'update']);
$router->post('/admin/products/{id}/deactivate', [$adminProductController, 'deactivate']);
$router->post('/admin/products/{id}/activate', [$adminProductController, 'activate']);
$router->post('/admin/logout', [$authController, 'logout']);
