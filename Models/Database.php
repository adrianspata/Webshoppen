<?php

require_once(__DIR__ . '/CartItem.php');
require_once(__DIR__ . '/UserDatabase.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/Product.php');

class Database
{
    public $pdo;
    private $usersDatabase;

    function getUsersDatabase()
    {
        return $this->usersDatabase;
    }

    function __construct()
    {
        $host = $_ENV['HOST'];
        $db = $_ENV['DB'];
        $user = $_ENV['USER'];
        $pass = $_ENV['PASSWORD'];
        $port = $_ENV['PORT'];

        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8";
        $this->pdo = new PDO($dsn, $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->initDatabase();
        $this->usersDatabase = new UserDatabase($this->pdo);
        $this->usersDatabase->setupUsers();
        $this->usersDatabase->seedUsers();
    }

    private function initDatabase()
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS Products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(50),
                price INT,
                stockLevel INT,
                categoryName VARCHAR(50),
                popularityFactor INT DEFAULT 0,
                image_url VARCHAR(255) DEFAULT '/assets/images/default.jpg'
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS Cart (
                id INT AUTO_INCREMENT PRIMARY KEY,
                productId INT NOT NULL,
                sessionId VARCHAR(255),
                userId INT,
                quantity INT DEFAULT 1
            )
        ");
    }

    public function getProduct($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Product');
        return $stmt->fetch();
    }

    public function updateProduct($product)
    {
        $stmt = $this->pdo->prepare("
            UPDATE Products 
            SET title = :title, price = :price, stockLevel = :stockLevel, 
                categoryName = :categoryName, popularityFactor = :popularityFactor 
            WHERE id = :id
        ");
        $stmt->execute([
            'title' => $product->title,
            'price' => $product->price,
            'stockLevel' => $product->stockLevel,
            'categoryName' => $product->categoryName,
            'popularityFactor' => $product->popularityFactor,
            'id' => $product->id
        ]);
    }

    public function deleteProduct($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM Products WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function insertProduct($title, $stockLevel, $price, $categoryName, $popularityFactor, $image_url = null)
    {
        $image_url = $image_url ?? '/assets/images/default.jpg';

        $stmt = $this->pdo->prepare("
            INSERT INTO Products (title, price, stockLevel, categoryName, popularityFactor, image_url)
            VALUES (:title, :price, :stockLevel, :categoryName, :popularityFactor, :image_url)
        ");
        $stmt->execute([
            'title' => $title,
            'price' => $price,
            'stockLevel' => $stockLevel,
            'categoryName' => $categoryName,
            'popularityFactor' => $popularityFactor,
            'image_url' => $image_url
        ]);
    }

    public function searchProducts($q, $sortCol, $sortOrder, $pageNo, $pageSize = 10)
    {
        if (!in_array($sortCol, ["title", "price"])) {
            $sortCol = "title";
        }

        if (!in_array($sortOrder, ["asc", "desc"])) {
            $sortOrder = "asc";
        }

        $offset = ($pageNo - 1) * $pageSize;

        // Fetch paginated products
        $sqlProducts = "SELECT * FROM Products 
                    WHERE title LIKE :q OR categoryName LIKE :q 
                    ORDER BY $sortCol $sortOrder 
                    LIMIT :offset, :pageSize";
        $stmt = $this->pdo->prepare($sqlProducts);
        $stmt->bindValue(':q', '%' . $q . '%');
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->bindValue(':pageSize', (int) $pageSize, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_CLASS, 'Product');

        // Count total number of matching products
        $sqlCount = "SELECT COUNT(*) FROM Products 
                 WHERE title LIKE :q OR categoryName LIKE :q";
        $countStmt = $this->pdo->prepare($sqlCount);
        $countStmt->bindValue(':q', '%' . $q . '%');
        $countStmt->execute();
        $totalCount = $countStmt->fetchColumn();
        $numPages = ceil($totalCount / $pageSize);

        return [
            "data" => $products,
            "num_pages" => $numPages
        ];
    }


    public function getAllProducts($sortCol = "id", $sortOrder = "asc")
    {
        $allowedCols = ["id", "title", "price", "stockLevel", "categoryName"];
        if (!in_array($sortCol, $allowedCols))
            $sortCol = "id";
        if (!in_array($sortOrder, ["asc", "desc"]))
            $sortOrder = "asc";

        $stmt = $this->pdo->query("SELECT * FROM Products ORDER BY $sortCol $sortOrder");
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Product');
    }

    public function getPopularProducts()
    {
        $stmt = $this->pdo->query("SELECT * FROM Products ORDER BY popularityFactor DESC LIMIT 10");
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Product');
    }

    public function getCategoryProducts($catName)
    {
        if (empty($catName) || strtolower($catName) === "all") {
            $stmt = $this->pdo->query("SELECT * FROM Products");
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM Products WHERE categoryName = :categoryName");
            $stmt->execute(['categoryName' => $catName]);
            return $stmt->fetchAll(PDO::FETCH_CLASS, 'Product');
        }

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Product');
    }

    public function getAllCategories()
    {
        $stmt = $this->pdo->query("SELECT DISTINCT categoryName FROM Products WHERE categoryName IS NOT NULL AND categoryName != ''");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }


    public function getCartItems($sessionId, $userId = null)
    {
        $query = "
            SELECT c.productId, c.quantity, p.title AS productName, p.price AS productPrice, 
                   (p.price * c.quantity) AS rowPrice
            FROM Cart c
            JOIN Products p ON c.productId = p.id
            WHERE c.sessionId = :sessionId" . ($userId ? " OR c.userId = :userId" : "");

        $params = ['sessionId' => $sessionId];
        if ($userId) {
            $params['userId'] = $userId;
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'CartItem');
    }

    public function addOrUpdateCartItem($userId, $sessionId, $productId, $quantity)
    {
        $stmt = $this->pdo->prepare($userId
            ? "UPDATE Cart SET quantity = ? WHERE userId = ? AND productId = ?"
            : "UPDATE Cart SET quantity = ? WHERE sessionId = ? AND productId = ?");
        $stmt->execute($userId ? [$quantity, $userId, $productId] : [$quantity, $sessionId, $productId]);

        if ($stmt->rowCount() === 0) {
            $stmt = $this->pdo->prepare($userId
                ? "INSERT INTO Cart (userId, productId, quantity) VALUES (?, ?, ?)"
                : "INSERT INTO Cart (sessionId, productId, quantity) VALUES (?, ?, ?)");
            $stmt->execute($userId ? [$userId, $productId, $quantity] : [$sessionId, $productId, $quantity]);
        }
    }

    public function deleteCartItem($userId, $sessionId, $productId)
    {
        $sql = "DELETE FROM Cart WHERE productId = :productId AND ";
        $sql .= $userId ? "userId = :userId" : "sessionId = :sessionId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':productId', $productId, PDO::PARAM_INT);
        if ($userId) {
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':sessionId', $sessionId, PDO::PARAM_STR);
        }
        return $stmt->execute();
    }
}
