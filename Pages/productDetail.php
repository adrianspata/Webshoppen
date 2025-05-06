<?php
require_once(__DIR__ . "/../Models/Database.php");
require_once(__DIR__ . "/../Models/Cart.php");
require_once(__DIR__ . "/../Models/Product.php");
require_once(__DIR__ . "/../components/Footer.php");

session_start();

$db = new Database();

$productId = $_GET['id'] ?? null;

if (!$productId) {
    echo "Ingen produkt angiven.";
    exit;
}

$product = $db->getProduct($productId);

if (!$product) {
    echo "Produkten hittades inte.";
    exit;
}

// Bildhantering: sätt bild med fallback
$image = '/assets/images/default.jpg';
if (!empty($product->image_url)) {
    $image = '/' . ltrim($product->image_url, '/');
}

$session_id = session_id();
$userId = null;
if ($db->getUsersDatabase()->getAuth()->isLoggedIn()) {
    $userId = $db->getUsersDatabase()->getAuth()->getUserId();
}

$cart = new Cart($db, $session_id, $userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $cart->addItem($productId, 1);
    $message = "Produkten har lagts till i varukorgen!";
}
?>

<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product->title); ?></title>
    <link href="/css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="/">Fruit Life</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">Kategorier</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/category">All Products</a></li>
                            <li>
                                <hr class="dropdown-divider" />
                            </li>
                            <?php
                            foreach ($db->getAllCategories() as $cat) {
                                echo "<li><a class='dropdown-item' href='/category?catname=$cat'>$cat</a></li>";
                            }
                            ?>
                        </ul>
                    </li>
                    <?php if ($db->getUsersDatabase()->getAuth()->isLoggedIn()) { ?>
                        <li class="nav-item"><a class="nav-link" href="/user/logout">Logout</a></li>
                    <?php } else { ?>
                        <li class="nav-item"><a class="nav-link" href="/user/login">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="/user/register">Create account</a></li>
                    <?php } ?>
                </ul>

                <form action="/search" method="GET">
                    <input type="text" name="q" placeholder="Search" class="form-control">
                </form>

                <?php if ($db->getUsersDatabase()->getAuth()->isLoggedIn()) { ?>
                    Current user: <?php echo $db->getUsersDatabase()->getAuth()->getUsername(); ?>
                <?php } ?>

                <form class="d-flex">
                    <a class="btn btn-outline-dark" href="/viewCart">
                        <i class="bi-cart-fill me-1"></i>
                        Cart
                        <span id="cartCount" class="badge bg-dark text-white ms-1 rounded-pill">
                            <?php echo $cart->getItemsCount(); ?>
                        </span>
                    </a>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="row align-items-center">
            <div class="col-md-6 text-center mb-4 mb-md-0">
                <img src="<?php echo htmlspecialchars($image); ?>"
                    alt="<?php echo htmlspecialchars($product->title); ?>" class="img-fluid rounded shadow"
                    style="max-height: 500px;">
            </div>

            <div class="col-md-6">
                <h1 class="mb-3"><?php echo htmlspecialchars($product->title); ?></h1>
                <p class="fs-4 text-primary fw-bold">$<?php echo number_format($product->price, 2); ?></p>
                <p class="text-muted"><?php echo htmlspecialchars($product->categoryName); ?></p>

                <?php if (!empty($product->description)): ?>
                    <div class="mt-3">
                        <p><?php echo nl2br(htmlspecialchars($product->description)); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" class="mt-4">
                    <input type="hidden" name="add_to_cart" value="1">
                    <button type="submit" class="btn btn-success btn-lg" <?php echo ((int) $product->stockLevel <= 0) ? 'disabled' : ''; ?>>
                        <?php echo ((int) $product->stockLevel <= 0) ? 'Out of stock' : 'Add to cart'; ?>
                    </button>
                </form>

                <a href="/" class="btn btn-link mt-3">← Back to home</a>
            </div>
        </div>
    </div>

    <?php Footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>