<?php

require_once("Utils/router.php"); // LADDAR IN ROUTER KLASSEN
require_once("vendor/autoload.php"); // LADDA ALLA DEPENDENCIES FROM VENDOR
//  :: en STATIC funktion
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
// Pilar istf .
// \ istf .

// import * as dotenv from 'dotenv';



$router = new Router();
$router->addRoute('/', function () {
    require_once(__DIR__ . '/Pages/index.php');
});
$router->addRoute('/category', function () {
    require_once(__DIR__ . '/Pages/category.php');
});
$router->addRoute('/admin/products', function () {
    require_once(__DIR__ . '/Pages/admin.php');
});
$router->addRoute('/admin/edit', function () {
    require_once(__DIR__ . '/Pages/edit.php');
});
$router->addRoute('/admin/new', function () {
    require_once(__DIR__ . '/Pages/new.php');
});
$router->addRoute('/admin/delete', function () {
    require_once(__DIR__ . '/Pages/delete.php');
});

$router->addRoute('/user/login', function () {
    require_once(__DIR__ . '/Pages/users/login.php');
});
$router->addRoute('/user/logout', function () {
    require_once(__DIR__ . '/Pages/users/logout.php');
});

$router->addRoute('/user/register', function () {
    require_once(__DIR__ . '/Pages/users/register.php');
});

$router->addRoute('/user/registerThanks', function () {
    require_once(__DIR__ . '/Pages/users/registerThanks.php');
});

$router->addRoute('/search', function () {
    require_once(__DIR__ . '/Pages/search.php');
});

$router->addRoute('/api/addToCart', function () {
    require_once(__DIR__ . '/ApiCode/cart.php');
});

$router->addRoute('/viewCart', function () {
    require_once(__DIR__ . '/Pages/viewCart.php');
});

$router->addRoute('/addToCart', function () {
    require_once(__DIR__ . '/Pages/addToCart.php');
});

$router->addRoute('/removeFromCart', function () { // Betyder ta bort EN 
    require_once(__DIR__ . '/Pages/removeFromCart.php');
});

$router->dispatch();
?>