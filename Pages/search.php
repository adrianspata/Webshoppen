<?php
require_once("Models/Product.php");
require_once("components/Footer.php");
require_once("Models/Database.php");
require_once("components/SingleProduct.php");

$dbContext = new Database();

$q = $_GET['q'] ?? "";
$sortCol = $_GET['sortCol'] ?? "";
$sortOrder = $_GET['sortOrder'] ?? "";
$pageNo = $_GET['pageNo'] ?? "1";
$pageSize = $_GET['pageSize'] ?? "10";

$result = $dbContext->searchProducts($q, $sortCol, $sortOrder, $pageNo, $pageSize);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Shop Homepage - Start Bootstrap Template</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="/css/styles.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/19719cda05.js" crossorigin="anonymous"></script>
</head>

<body>
    <!-- Navigation-->
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

                <!-- Search -->
                <form action="/search" method="GET" class="d-flex me-3">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search"
                        class="form-control me-2">
                    <button type="submit" class="btn btn-outline-secondary">Search</button>
                </form>

                <!-- Cart -->
                <div class="d-flex">
                    <a class="btn btn-outline-dark" href="/viewCart">
                        <i class="bi-cart-fill me-1"></i>
                        Cart
                        <span class="badge bg-dark text-white ms-1 rounded-pill">0</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="bg-dark py-5">
        <div class="container px-4 px-lg-5 my-5">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bolder">Fruit Life</h1>
                <p class="lead fw-normal text-white-50 mb-0">We have all your favourite fruits!</p>
            </div>
        </div>
    </header>

    <!-- Sorting buttons -->
    <section class="py-5">
        <div class="container px-4 px-lg-5 mt-5">
            <div class="text-center mb-4">
                <a href="?sortCol=title&sortOrder=asc&q=<?php echo htmlspecialchars($q); ?>"
                    class="btn btn-secondary">A-Z <i class="fa-solid fa-arrow-up"></i></a>
                <a href="?sortCol=title&sortOrder=desc&q=<?php echo htmlspecialchars($q); ?>"
                    class="btn btn-secondary">Z-A <i class="fa-solid fa-arrow-down"></i></a>
                <a href="?sortCol=price&sortOrder=asc&q=<?php echo htmlspecialchars($q); ?>"
                    class="btn btn-secondary">Low-High <i class="fa-solid fa-arrow-up"></i></a>
                <a href="?sortCol=price&sortOrder=desc&q=<?php echo htmlspecialchars($q); ?>"
                    class="btn btn-secondary">High-Low <i class="fa-solid fa-arrow-down"></i></a>
            </div>

            <!-- Product grid -->
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <?php foreach ($result["data"] as $prod) {
                    SingleProduct($prod);
                } ?>
            </div>

            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $result["num_pages"]; $i++) {
                        if ($i == $pageNo) {
                            echo "<li class='page-item active'><span class='page-link'>$i</span></li>";
                        } else {
                            echo "<li class='page-item'><a class='page-link' href='?q=$q&pageNo=$i&sortCol=$sortCol&sortOrder=$sortOrder'>$i</a></li>";
                        }
                    } ?>
                </ul>
            </nav>
        </div>
    </section>

    <!-- Footer -->
    <?php Footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>