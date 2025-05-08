<?php

require_once("Models/Product.php");
require_once("components/Footer.php");
require_once("Models/Database.php");
require_once(__DIR__ . "/../Models/Cart.php");



$dbContext = new Database();

$userId = null;
$session_id = null;

if ($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()) {
    $userId = $dbContext->getUsersDatabase()->getAuth()->getUserId();
}
//$cart = $dbContext->getCartByUser($userId);
$session_id = session_id();

$cart = new Cart($dbContext, $session_id, $userId);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Shop Homepage - Start Bootstrap Template</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="/css/styles.css" rel="stylesheet" />
</head>

<body>
    <!-- Navigation-->
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

                <!-- Inloggad användare -->
                <?php if ($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()) { ?>
                    <span class="me-3">Welcome:
                        <?php echo htmlspecialchars($dbContext->getUsersDatabase()->getAuth()->getUsername()); ?></span>
                <?php } ?>

                <!-- Cart -->
                <div class="d-flex">
                    <a class="btn btn-outline-dark" href="/viewCart">
                        <i class="bi-cart-fill me-1"></i>
                        Cart
                        <span class="badge bg-dark text-white ms-1 rounded-pill">
                            <?php echo $cart->getItemsCount(); ?>
                        </span>
                    </a>
                </div>

    </nav>
    <!-- Header-->
    <header class="bg-dark py-5">
        <div class="container px-4 px-lg-5 my-5">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bolder">Fruit Life</h1>
                <p class="lead fw-normal text-white-50 mb-0">We have all your favourite fruits!</p>
            </div>
        </div>
    </header>
    <!-- Section-->
    <section class="py-5">
        <div class="container px-4 px-lg-5 mt-5">

            <!-- Sektionstitel -->
            <div class="text-center mb-5">
                <h2 class="fw-bolder">Most Popular Products</h2>
                <p class="text-muted">These top picks are loved by our customers</p>
            </div>

            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <?php
                foreach ($dbContext->getPopularProducts() as $prod) {
                    ?>
                    <div class="col mb-5">
                        <div class="card h-100 position-relative">
                            <a href="/Pages/productDetail.php?id=<?php echo $prod->id; ?>" class="stretched-link"></a>

                            <!-- Product image -->
                            <?php $image = !empty($prod->image_url) ? $prod->image_url : 'assets/default.jpg'; ?>
                            <img class="card-img-top" src="<?php echo htmlspecialchars($image); ?>"
                                alt="<?php echo htmlspecialchars($prod->title); ?>" />


                            <!-- Product details-->
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <h5 class="fw-bolder"><?php echo $prod->title; ?></h5>
                                    $<?php echo $prod->price; ?>.00
                                </div>
                            </div>

                            <!-- Product actions-->
                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                <div class="text-center">
                                    <a class="btn btn-outline-dark mt-auto"
                                        href="/cart.php?productId=<?php echo $prod->id; ?>">
                                        Add to cart
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>




    <!-- Footer-->
    <?php Footer(); ?>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="/scripts/cart.js"></script>




</body>

</html>