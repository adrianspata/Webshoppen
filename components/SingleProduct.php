<?php
function SingleProduct($prod)
{
    ?>
    <div class="col mb-5">
        <div class="card h-100 position-relative">
            <a href="/Pages/productDetail.php?id=<?php echo $prod->id; ?>" class="stretched-link"></a>

            <!-- Produktbild -->
            <img class="card-img-top" src="<?php echo htmlspecialchars($prod->image_url); ?>"
                alt="<?php echo htmlspecialchars($prod->title); ?>" />

            <!-- Produktinfo -->
            <div class="card-body p-4">
                <div class="text-center">
                    <h5 class="fw-bolder"><?php echo $prod->title; ?></h5>
                    $<?php echo $prod->price; ?>.00
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>