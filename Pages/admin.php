<?php
session_start();

require_once("Models/Product.php");
require_once("Models/Database.php");
require_once("Models/Cart.php");
require_once("components/Footer.php");

$dbContext = new Database();

// Hämta sorteringsparametrar
$sortCol = $_GET['sortCol'] ?? "";
$sortOrder = $_GET['sortOrder'] ?? "";

// Cart-setup
$session_id = session_id();
$userId = null;
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
    <title>Admin - Fruit Life</title>
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

    <!-- Admin Table -->
    <section class="py-5">
        <div class="container px-4 px-lg-5 mt-5">
            <a href="/admin/new" class="btn btn-primary mb-3">Create new</a>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name
                            <a href="/admin/products?sortCol=title&sortOrder=asc"><i
                                    class="bi bi-arrow-down-circle-fill"></i></a>
                            <a href="/admin/products?sortCol=title&sortOrder=desc"><i
                                    class="bi bi-arrow-up-circle-fill"></i></a>
                        </th>
                        <th>Category
                            <a href="/admin/products?sortCol=categoryName&sortOrder=asc"><i
                                    class="bi bi-arrow-down-circle-fill"></i></a>
                            <a href="/admin/products?sortCol=categoryName&sortOrder=desc"><i
                                    class="bi bi-arrow-up-circle-fill"></i></a>
                        </th>
                        <th>Price
                            <a href="/admin/products?sortCol=price&sortOrder=asc"><i
                                    class="bi bi-arrow-down-circle-fill"></i></a>
                            <a href="/admin/products?sortCol=price&sortOrder=desc"><i
                                    class="bi bi-arrow-up-circle-fill"></i></a>
                        </th>
                        <th>Stock level
                            <a href="/admin/products?sortCol=stockLevel&sortOrder=asc"><i
                                    class="bi bi-arrow-down-circle-fill"></i></a>
                            <a href="/admin/products?sortCol=stockLevel&sortOrder=desc"><i
                                    class="bi bi-arrow-up-circle-fill"></i></a>
                        </th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($dbContext->getAllProducts($sortCol, $sortOrder) as $prod) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prod->title); ?></td>
                            <td><?php echo htmlspecialchars($prod->categoryName); ?></td>
                            <td>$<?php echo number_format($prod->price, 2); ?></td>
                            <td><?php echo htmlspecialchars($prod->stockLevel); ?></td>
                            <td>
                                <a href="edit?id=<?php echo $prod->id; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="delete?id=<?php echo $prod->id; ?>" class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php Footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>