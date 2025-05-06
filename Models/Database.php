<?php

require_once(__DIR__ . '/UserDatabase.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/Product.php');

// Hur kan man strukturera klasser
// Hir kan man struktirera filer? Folders + subfolders
// NAMESPACES       

// LÄS IN ALLA  .env VARIABLER till $_ENV i PHP



class Database
{
    public $pdo; // PDO är PHP Data Object - en klass som finns i PHP för att kommunicera med databaser
    // I $pdo finns nu funktioner (dvs metoder!) som kan användas för att kommunicera med databasen

    private $usersDatabase;
    function getUsersDatabase()
    {
        return $this->usersDatabase;
    }


    // Note to Stefan STATIC så inte initieras varje gång

    // SKILJ PÅ CONFIGURATION OCH KOD

    function __construct()
    {
        $host = $_ENV['HOST'];
        $db = $_ENV['DB'];
        $user = $_ENV['USER'];
        $pass = $_ENV['PASSWORD'];
        $port = $_ENV['PORT'];

        $dsn = "mysql:host=$host:$port;dbname=$db"; // connection string
        $this->pdo = new PDO($dsn, $user, $pass);
        $this->initDatabase();
        $this->modifyDatabase();
        $this->initData();
        $this->usersDatabase = new UserDatabase($this->pdo);
        $this->usersDatabase->setupUsers();
        $this->usersDatabase->seedUsers();
    }

    function addProductIfNotExists($title, $price, $stockLevel, $categoryName, $popularityFactor)
    {
        $query = $this->pdo->prepare("SELECT * FROM Products WHERE title = :title");
        $query->execute(['title' => $title]);
        if ($query->rowCount() == 0) {
            $image_url = '/assets/images/default.jpg'; // lägg till detta
            $this->insertProduct($title, $stockLevel, $price, $categoryName, $popularityFactor, $image_url);
        }
    }



    function initData()
    {
        $sql = "SELECT COUNT(*) FROM Products";
        $res = $this->pdo->query($sql);
        $count = $res->fetchColumn();
        if ($count > 0) {
            return;
        }
        $faker = \Faker\Factory::create();
        $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));

        for ($i = 0; $i < 100; $i++) {
            $title = $faker->productName();
            $price = $faker->numberBetween(1, 100);
            $stockLevel = $faker->numberBetween(1, 100);
            $categoryName = $faker->category();
            $popularityFactor = $faker->numberBetween(1, 100);
            $this->addProductIfNotExists($title, $price, $stockLevel, $categoryName, $popularityFactor);
        }
    }

    function columnExists($pdo, $table, $column)
    {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM $table WHERE  field = :column");
        $stmt->execute(['column' => $column]);
        return $stmt->rowCount() > 0;
    }

    function modifyDatabase()
    {
        if ($this->columnExists($this->pdo, 'Products', 'color')) {
            return;
        }
        $this->pdo->query('ALTER TABLE Products ADD COLUMN color varchar(20) DEFAULT NULL');
    }

    function initDatabase()
    {
        // Skapa Products-tabellen
        $this->pdo->query('CREATE TABLE IF NOT EXISTS Products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(50),
            price INT,
            stockLevel INT,
            categoryName VARCHAR(50),
            popularityFactor INT DEFAULT 0            
        )');

        // Skapa Cart-tabellen
        $this->pdo->query('CREATE TABLE IF NOT EXISTS Cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            productId INT,
            sessionId VARCHAR(255),
            userId INT DEFAULT NULL,
            quantity INT DEFAULT 1
        )');
    }


    function getProduct($id)
    {
        $query = $this->pdo->prepare("SELECT * FROM Products WHERE id = :id");
        $query->execute(['id' => $id]);
        $query->setFetchMode(PDO::FETCH_CLASS, 'Product');
        return $query->fetch();
    }

    function updateProduct($product)
    {
        $s = "UPDATE Products SET title = :title," .
            " price = :price, stockLevel = :stockLevel, categoryName = :categoryName, popularityFactor=:popularityFactor WHERE id = :id";
        $query = $this->pdo->prepare($s);
        $query->execute([
            'title' => $product->title,
            'price' => $product->price,
            'stockLevel' => $product->stockLevel,
            'categoryName' => $product->categoryName,
            'id' => $product->id,
            'popularityFactor' => $product->popularityFactor
        ]);
    }

    function deleteProduct($id)
    {
        $query = $this->pdo->prepare("DELETE FROM Products WHERE id = :id");
        $query->execute(['id' => $id]);
    }

    function insertProduct($title, $stockLevel, $price, $categoryName, $popularityFactor, $image_url = null)
    {
        if (empty($image_url)) {
            $image_url = '/assets/images/default.jpg'; // 🔁 sätt standardbild om ingen bild anges
        }

        $sql = "INSERT INTO Products (title, price, stockLevel, categoryName, popularityFactor, image_url)
            VALUES (:title, :price, :stockLevel, :categoryName, :popularityFactor, :image_url)";
        $query = $this->pdo->prepare($sql);
        $query->execute([
            'title' => $title,
            'price' => $price,
            'stockLevel' => $stockLevel,
            'categoryName' => $categoryName,
            'popularityFactor' => $popularityFactor,
            'image_url' => $image_url
        ]);
    }






    function searchProducts($q, $sortCol, $sortOrder, $pageNo, $pageSize = 10)
    { // $q = oo
        if (!in_array($sortCol, ["title", "price"])) { // title123312132312321
            $sortCol = "title";
        }
        if (!in_array($sortOrder, ["asc", "desc"])) {
            $sortOrder = "asc";
        }

        $sqlProducts = "SELECT * FROM Products WHERE title LIKE :q OR categoryName LIKE :q ORDER BY $sortCol $sortOrder";
        $sqlCount = str_replace("SELECT * FROM ", "SELECT CEIL (COUNT(*)/$pageSize) FROM ", $sqlProducts);

        // LIMIT 
        $offset = ($pageNo - 1) * $pageSize; // START POSITIONEN

        // $sqlProducts = $sqlProducts +  " LIMIT $offset, $pageSize"; // LIMIT 0, 10
        // $sqlProducts +=  " LIMIT $offset, $pageSize"; // LIMIT 0, 10
        //$sqlProducts =   $sqlProducts . " LIMIT $offset, $pageSize"; // LIMIT 0, 10
        $sqlProducts .= " LIMIT $offset, $pageSize"; // LIMIT 0, 10

        $query = $this->pdo->prepare($sqlProducts); // Products är TABELL
        $query->execute(['q' => "%$q%"]);
        $data = $query->fetchAll(PDO::FETCH_CLASS, 'Product'); // $data  innehåller alla produkter som matchar sökningen


        $query = $this->pdo->prepare($sqlCount); // Products är TABELL
        $query->execute(['q' => "%$q%"]);
        $num_pages = $query->fetchColumn();   // $num_pages  innehåller antalet sidor som finns i databasen

        // arrayen["data"] istf // arrayen[0]
        return ["data" => $data, "num_pages" => $num_pages]; // returnerar en array med två element: $data och $num_pages
    }

    // Vad är en array?
    // en array är en samling av värden
    // värdena når vi genom indexnummer 
    // $players[0] 
    // $players[1] 

    // Vad är en associativ array? (dictionaries i Python, maps)
    // en associativ array är en samling av värden
    // värdena når vi genom NAMN (keys) 
    // $players['forward'] 
    // $players['goalie'] 


    // $result = $this->searchProducts($q,$sortCol, $sortOrder,$pageNo,$pageSize); // $result är en array med två element: $data och $num_pages
    // $data = $result[0]; // $data innehåller alla produkter som matchar sökningen    
    // $num_pages = $result[1]; // $num_pages innehåller antalet sidor som finns i databasen


    //function getAllProducts($sortCol, $sortOrder){
    function getAllProducts($sortCol = "id", $sortOrder = "asc")
    {
        if (!in_array($sortCol, ["id", "categoryName", "title", "price", "stockLevel"])) {
            $sortCol = "id";
        }
        if (!in_array($sortOrder, ["asc", "desc"])) {
            $sortOrder = "asc";
        }

        // SELECT * FROM Products ORDER BY  id asc
        $query = $this->pdo->query("SELECT * FROM Products ORDER BY $sortCol $sortOrder"); // Products är TABELL 
        return $query->fetchAll(PDO::FETCH_CLASS, 'Product'); // Product är PHP Klass
    }
    function getPopularProducts()
    {
        $query = $this->pdo->query("SELECT * FROM Products ORDER BY popularityFactor DESC LIMIT 10"); // Products är TABELL 
        return $query->fetchAll(PDO::FETCH_CLASS, 'Product'); // Product är PHP Klass
    }

    function getCategoryProducts($catName)
    {
        if ($catName == "") {
            $query = $this->pdo->query("SELECT * FROM Products"); // Products är TABELL 
            return $query->fetchAll(PDO::FETCH_CLASS, 'Product'); // Product är PHP Klass
        }
        $query = $this->pdo->prepare("SELECT * FROM Products WHERE categoryName = :categoryName");
        $query->execute(['categoryName' => $catName]);
        return $query->fetchAll(PDO::FETCH_CLASS, 'Product');
    }
    function getAllCategories()
    {
        // SELECT DISTINCT categoryName FROM Products
        $data = $this->pdo->query('SELECT DISTINCT categoryName FROM Products')->fetchAll(PDO::FETCH_COLUMN);
        return $data;
    }

    function getCartItems($sessionId, $userId = null)
    {
        $queryStr = "SELECT c.productId, c.quantity, p.title AS productName, p.price AS productPrice, (p.price * c.quantity) AS rowPrice
                 FROM Cart c
                 JOIN Products p ON c.productId = p.id
                 WHERE c.sessionId = :sessionId";

        $params = ['sessionId' => $sessionId];

        if ($userId !== null) {
            $queryStr .= " OR c.userId = :userId";
            $params['userId'] = $userId;
        }

        $query = $this->pdo->prepare($queryStr);
        $query->execute($params);

        // ✅ returnera resultatet som CartItem-objekt
        return $query->fetchAll(PDO::FETCH_CLASS, 'CartItem');
    }


    public function updateCartItem($userId, $sessionId, $productId, $quantity)
    {
        // Antingen måste userId eller sessionId vara satt
        if (!$userId && !$sessionId) {
            throw new Exception("Varken userId eller sessionId är angivet.");
        }

        // Skapa SQL beroende på om användaren är inloggad eller gäst
        if ($userId) {
            $stmt = $this->pdo->prepare("
                UPDATE cart_items 
                SET quantity = ? 
                WHERE user_id = ? AND product_id = ?
            ");
            return $stmt->execute([$quantity, $userId, $productId]);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE cart_items 
                SET quantity = ? 
                WHERE session_id = ? AND product_id = ?
            ");
            return $stmt->execute([$quantity, $sessionId, $productId]);
        }
    }

    public function addOrUpdateCartItem($userId, $sessionId, $productId, $quantity)
    {
        // Försök uppdatera först
        if ($userId) {
            $stmt = $this->pdo->prepare("UPDATE Cart SET quantity = ? WHERE userId = ? AND productId = ?");
            $stmt->execute([$quantity, $userId, $productId]);

            if ($stmt->rowCount() === 0) {
                // Ingen rad uppdaterades – lägg till
                $stmt = $this->pdo->prepare("INSERT INTO Cart (userId, productId, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $productId, $quantity]);
            }
        } else {
            $stmt = $this->pdo->prepare("UPDATE Cart SET quantity = ? WHERE sessionId = ? AND productId = ?");
            $stmt->execute([$quantity, $sessionId, $productId]);

            if ($stmt->rowCount() === 0) {
                // Ingen rad uppdaterades – lägg till
                $stmt = $this->pdo->prepare("INSERT INTO Cart (sessionId, productId, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$sessionId, $productId, $quantity]);
            }
        }
    }

}
?>