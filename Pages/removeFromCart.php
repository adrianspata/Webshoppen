<?php
require_once("Models/Database.php");
require_once("Models/Cart.php");

session_start();

// Initiera databaskoppling
$dbContext = new Database();

$userId = null;
$sessionId = session_id();

if ($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()) {
    $userId = $dbContext->getUsersDatabase()->getAuth()->getUserId();
}

// Hämta produkt-ID
$productId = isset($_GET['productId']) ? (int) $_GET['productId'] : null;
$removeCount = isset($_GET['removeCount']) ? (int) $_GET['removeCount'] : 1;
$fromPage = isset($_GET['fromPage']) ? $_GET['fromPage'] : '/viewCart';

if ($productId === null || $productId <= 0) {
    header("Location: $fromPage");
    exit;
}

// Initiera Cart-objektet
$cart = new Cart($dbContext, $sessionId, $userId);

// Om användaren klickat på DELETE ALL (ta bort hela raden)
if (isset($_GET['removeCount']) && $removeCount > 0) {
    $cart->removeItem($productId, $removeCount);
} else {
    // Annars ta bort 1
    $cart->removeItem($productId, 1);
}

// Skicka tillbaka användaren
header("Location: $fromPage");
exit;
