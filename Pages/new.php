<?php
session_start();

require_once('Models/Product.php');
require_once("components/Footer.php");
require_once('Models/Database.php');
require_once("Models/Cart.php");

$dbContext = new Database();

$session_id = session_id();
$userId = null;

if ($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()) {
    $userId = $dbContext->getUsersDatabase()->getAuth()->getUserId();
}

$cart = new Cart($dbContext, $session_id, $userId);
$cartCount = $cart->getItemsCount();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $stockLevel = $_POST['stockLevel'];
    $price = $_POST['price'];
    $categoryName = $_POST['categoryName'];
    $popularityFactor = $_POST['popularityFactor'];

    // Hantera filuppladdning
    $image_url = '/assets/images/default.jpg';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/';
        $filename = basename($_FILES['product_image']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath)) {
            $image_url = '/assets/images/' . $filename;
        }
    }

    $dbContext->insertProduct($title, $stockLevel, $price, $categoryName, $popularityFactor, $image_url);
    header("Location: /admin/products");
    exit;
} else {
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>New - Fruit Life</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="/css/styles.css" rel="stylesheet" />
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="/">Fruit Life</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent"><span class="navbar-toggler-icon"></span></button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left side -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button"
                            data-bs-toggle="dropdown">Kategorier</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/category">All</a></li>
                            <li>
                                <hr class="dropdown-divider" />
                            </li>
                            <?php
                            foreach ($dbContext->getAllCategories() as $cat) {
                                echo "<li><a class='dropdown-item' href='/category?catname=$cat'>$cat</a></li>";
                            }
                            ?>
                        </ul>
                    </li>
                    <?php if ($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()) { ?>
                        <li class="nav-item"><a class="nav-link" href="/user/logout">Logout</a></li>
                    <?php } else { ?>
                        <li class="nav-item"><a class="nav-link" href="/user/login">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="/user/register">Create account</a></li>
                    <?php } ?>
                </ul>

                <!-- Search form -->
                <form action="/search" method="GET" class="d-flex me-3">
                    <input type="text" name="q" placeholder="Search" class="form-control me-2">
                    <button type="submit" class="btn btn-outline-secondary">Search</button>
                </form>

                <!-- Logged-in user -->
                <?php if ($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()) { ?>
                    <span class="me-3">Welcome:
                        <?php echo htmlspecialchars($dbContext->getUsersDatabase()->getAuth()->getUsername()); ?>
                    </span>
                <?php } ?>

                <!-- Cart -->
                <div class="d-flex">
                    <a class="btn btn-outline-dark" href="/viewCart">
                        <i class="bi-cart-fill me-1"></i>
                        Cart
                        <span class="badge bg-dark text-white ms-1 rounded-pill"><?php echo $cartCount; ?></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <section class="py-5">
        <div class="container px-4 px-lg-5 mt-5">



            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" class="form-control" name="title" required>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="text" class="form-control" name="price" required>
                </div>
                <div class="form-group">
                    <label for="stockLevel">Stock</label>
                    <input type="text" class="form-control" name="stockLevel" required>
                </div>
                <div class="form-group">
                    <label for="categoryName">Category name</label>
                    <input type="text" class="form-control" name="categoryName" required>
                </div>
                <div class="form-group">
                    <label for="popularityFactor">Popularity factor</label>
                    <input type="number" class="form-control" name="popularityFactor" value="0">
                </div>
                <div class="form-group">
                    <label for="product_image">Product Image</label>
                    <input type="file" class="form-control" name="product_image" accept="image/*">
                </div>

                <input type="submit" class="btn btn-primary mt-3" value="Add product">
            </form>

        </div>
    </section>



    <?php Footer(); ?>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="js/scripts.js"></script>

</body>

</html>

<!-- 
<input type="text" name="title" value="<?php echo $product->title ?>">
        <input type="text" name="price" value="<?php echo $product->price ?>">
        <input type="text" name="stockLevel" value="<?php echo $product->stockLevel ?>">
        <input type="text" name="categoryName" value="<?php echo $product->categoryName ?>">
        <input type="submit" value="Uppdatera"> -->