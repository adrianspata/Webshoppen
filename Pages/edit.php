<?php
session_start();

require_once(__DIR__ . '/../Models/Product.php');
require_once(__DIR__ . '/../Models/Database.php');
require_once(__DIR__ . '/../Models/Cart.php');
require_once(__DIR__ . '/../Utils/Validator.php');
require_once(__DIR__ . '/../components/Footer.php');

$dbContext = new Database();

$session_id = session_id();
$userId = null;
if ($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()) {
    $userId = $dbContext->getUsersDatabase()->getAuth()->getUserId();
}
$cart = new Cart($dbContext, $session_id, $userId);
$cartCount = $cart->getItemsCount();

$id = $_GET['id'];
$product = $dbContext->getProduct($id);
$v = new Validator($_POST);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product->title = $_POST['title'];
    $product->stockLevel = $_POST['stockLevel'];
    $product->price = $_POST['price'];
    $product->categoryName = $_POST['categoryName'];
    $product->popularityFactor = $_POST['popularityFactor'];

    $v->field('title')->required()->alpha_num([' '])->min_len(3)->max_len(50);
    $v->field('stockLevel')->required()->numeric()->min_val(0);
    $v->field('price')->required()->numeric()->min_val(0);
    $v->field('categoryName')->required()->min_len(2)->max_len(50);
    $v->field('popularityFactor')->required()->numeric()->min_val(0);

    if ($v->is_valid()) {
        $dbContext->updateProduct($product);
        header("Location: /admin/products");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Edit - Fruit Life</title>
    <link rel="icon" href="/assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="/css/styles.css" rel="stylesheet" />
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
                            <?php foreach ($dbContext->getAllCategories() as $cat) {
                                echo "<li><a class='dropdown-item' href='/category?catname=$cat'>$cat</a></li>";
                            } ?>
                        </ul>
                    </li>
                    <?php if ($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()) { ?>
                        <li class="nav-item"><a class="nav-link" href="/user/logout">Logout</a></li>
                    <?php } else { ?>
                        <li class="nav-item"><a class="nav-link" href="/user/login">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="/user/register">Create account</a></li>
                    <?php } ?>
                </ul>
                <form action="/search" method="GET" class="d-flex me-3">
                    <input type="text" name="q" placeholder="Search" class="form-control me-2">
                    <button type="submit" class="btn btn-outline-secondary">Search</button>
                </form>
                <?php if ($userId) { ?>
                    <span class="me-3">Welcome:
                        <?php echo htmlspecialchars($dbContext->getUsersDatabase()->getAuth()->getUsername()); ?></span>
                <?php } ?>
                <a class="btn btn-outline-dark" href="/viewCart">
                    <i class="bi-cart-fill me-1"></i>
                    Cart
                    <span class="badge bg-dark text-white ms-1 rounded-pill"><?php echo $cartCount; ?></span>
                </a>
            </div>
        </div>
    </nav>

    <section class="py-5">
        <div class="container px-4 px-lg-5 mt-5">
            <form method="POST">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text"
                        class="form-control <?php echo $v->get_error_message('title') != '' ? 'is-invalid' : '' ?>"
                        name="title" value="<?php echo htmlspecialchars($product->title); ?>">
                    <span class="invalid-feedback"><?php echo $v->get_error_message('title'); ?></span>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number"
                        class="form-control <?php echo $v->get_error_message('price') != '' ? 'is-invalid' : '' ?>"
                        name="price" value="<?php echo $product->price; ?>">
                    <span class="invalid-feedback"><?php echo $v->get_error_message('price'); ?></span>
                </div>
                <div class="form-group">
                    <label for="stockLevel">Stock</label>
                    <input type="text"
                        class="form-control <?php echo $v->get_error_message('stockLevel') != '' ? 'is-invalid' : '' ?>"
                        name="stockLevel" value="<?php echo $product->stockLevel; ?>">
                    <span class="invalid-feedback"><?php echo $v->get_error_message('stockLevel'); ?></span>
                </div>
                <div class="form-group">
                    <label for="categoryName">Category name:</label>
                    <input type="text"
                        class="form-control <?php echo $v->get_error_message('categoryName') != '' ? 'is-invalid' : '' ?>"
                        name="categoryName" value="<?php echo htmlspecialchars($product->categoryName); ?>">
                    <span class="invalid-feedback"><?php echo $v->get_error_message('categoryName'); ?></span>
                </div>
                <div class="form-group">
                    <label for="popularityFactor">Popularity factor</label>
                    <input type="number"
                        class="form-control <?php echo $v->get_error_message('popularityFactor') != '' ? 'is-invalid' : '' ?>"
                        name="popularityFactor" value="<?php echo $product->popularityFactor; ?>">
                    <span class="invalid-feedback"><?php echo $v->get_error_message('popularityFactor'); ?></span>
                </div>
                <input type="submit" class="btn btn-primary" value="Uppdatera">
            </form>
        </div>
    </section>

    <?php Footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>