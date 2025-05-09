<?php
require_once("Models/Product.php");
require_once("components/Footer.php");
require_once("Models/Database.php");
require_once("Models/Cart.php");
require_once("components/SingleProduct.php");

$dbContext = new Database();

$userId = null;
$session_id = session_id();

if ($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()) {
    $userId = $dbContext->getUsersDatabase()->getAuth()->getUserId();
}

$cart = new Cart($dbContext, $session_id, $userId);
$cartCount = $cart->getItemsCount();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Your Cart - Fruit Life</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="/css/styles.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/19719cda05.js" crossorigin="anonymous"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="/">Fruit Life</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button"
                            data-bs-toggle="dropdown">Kategorier</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/category">All</a></li>
                            <li>
                                <hr class="dropdown-divider" />
                            </li>
                            <?php foreach ($dbContext->getAllCategories() as $cat): ?>
                                <li><a class="dropdown-item"
                                        href="/category?catname=<?php echo $cat ?>"><?php echo $cat ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php if ($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()): ?>
                        <li class="nav-item"><a class="nav-link" href="/user/logout">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/user/login">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="/user/register">Create account</a></li>
                    <?php endif; ?>
                </ul>

                <form action="/search" method="GET" class="d-flex me-3">
                    <input type="text" name="q" placeholder="Search" class="form-control me-2">
                    <button type="submit" class="btn btn-outline-secondary">Search</button>
                </form>

                <?php if ($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()): ?>
                    <span class="me-3">Welcome:
                        <?php echo htmlspecialchars($dbContext->getUsersDatabase()->getAuth()->getUsername()); ?></span>
                <?php endif; ?>

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

    <header class="bg-dark py-5">
        <div class="container px-4 px-lg-5 my-5">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bolder">Your cart</h1>
            </div>
        </div>
    </header>

    <section class="py-5">
        <div class="container px-4 px-lg-5 mt-5">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart->getItems() as $item): ?>
                        <tr>
                            <td><?php echo $item->productName; ?></td>
                            <td><?php echo $item->quantity; ?></td>
                            <td><?php echo $item->productPrice; ?></td>
                            <td><?php echo $item->rowPrice; ?></td>
                            <td>
                                <?php $returnUrl = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>
                                <a href="/addToCart?productId=<?php echo $item->productId ?>&fromPage=<?php echo $returnUrl ?>"
                                    class="btn btn-primary">
                                    <i class="fa-solid fa-plus"></i>
                                </a>
                                <a href="/removeFromCart?productId=<?php echo $item->productId ?>&fromPage=<?php echo $returnUrl ?>"
                                    class="btn btn-danger">
                                    <i class="fa-solid fa-minus"></i>
                                </a>
                                <a href="/removeFromCart?removeCount=<?php echo $item->quantity ?>&productId=<?php echo $item->productId ?>&fromPage=<?php echo $returnUrl ?>"
                                    class="btn btn-warning" title="Delete all">
                                    <i class="fa-solid fa-trash"></i>
                                </a>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"></td>
                        <td><?php echo $cart->getTotalPrice(); ?></td>
                        <td><a href="/checkout" class="btn btn-success">Checkout</a></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>

    <?php Footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/scripts/cart.js"></script>
</body>

</html>