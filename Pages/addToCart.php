<?php
require_once(__DIR__ . '/../Models/Database.php');
require_once(__DIR__ . '/../Models/Cart.php');
session_start();

$db = new Database();
$userId = null;
$sessionId = session_id();

if ($db->getUsersDatabase()->getAuth()->isLoggedIn()) {
    $userId = $db->getUsersDatabase()->getAuth()->getUserId();
}

$productId = $_GET['productId'] ?? null;
if ($productId) {
    $cart = new Cart($db, $sessionId, $userId);
    $cart->addItem($productId, 1);
}

$redirect = $_GET['fromPage'] ?? '/';
header("Location: $redirect");
exit;
