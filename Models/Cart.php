<?php

require_once __DIR__ . '/CartItem.php';

class Cart
{
    private $dbContext;
    private $session_id;
    private $userId;
    private $cartItems = [];

    public function __construct($dbContext, $session_id, $userId = null)
    {
        $this->dbContext = $dbContext;
        $this->session_id = $session_id;
        $this->userId = $userId;

        // Hämta produkter från databasen
        $this->cartItems = $this->dbContext->getCartItems($session_id, $userId);
    }

    public function convertSessionToUser($userId, $newSessionId)
    {
        $this->dbContext->convertSessionToUser($this->session_id, $userId, $newSessionId);
        $this->userId = $userId;
        $this->session_id = $newSessionId;
    }

    public function addItem($productId, $quantity)
    {
        $item = $this->getCartItem($productId);
        if (!$item) {
            $item = new CartItem();
            $item->productId = $productId;
            $item->quantity = $quantity;
            $item->productName = ""; // kan sättas via DB
            $item->productPrice = 0; // sätts via JOIN i getCartItems
            $item->rowPrice = 0;
            array_push($this->cartItems, $item);
        } else {
            $item->quantity += $quantity;
        }

        $this->dbContext->addOrUpdateCartItem($this->userId, $this->session_id, $productId, $item->quantity);
    }

    public function removeItem($productId, $quantity)
    {
        $item = $this->getCartItem($productId);
        if (!$item) {
            return;
        }

        $item->quantity -= $quantity;

        if ($item->quantity <= 0) {
            $this->dbContext->deleteCartItem($this->userId, $this->session_id, $productId);
            $this->cartItems = array_filter($this->cartItems, function ($i) use ($productId) {
                return $i->productId !== $productId;
            });
        } else {
            $this->dbContext->addOrUpdateCartItem($this->userId, $this->session_id, $productId, $item->quantity);
        }
    }

    public function getCartItem($productId)
    {
        foreach ($this->cartItems as $item) {
            if ($item->productId == $productId) {
                return $item;
            }
        }
        return null;
    }

    public function getItemsCount()
    {
        $count = 0;
        foreach ($this->cartItems as $item) {
            $count += $item->quantity;
        }
        return $count;
    }

    public function getTotalPrice()
    {
        $total = 0;
        foreach ($this->cartItems as $item) {
            $total += $item->rowPrice;
        }
        return $total;
    }

    public function getItems()
    {
        return $this->cartItems;
    }

    public function clearCart()
    {
        $this->cartItems = [];
    }
}
