<?php
require_once __DIR__ . '/../config/database.php';

function getBannerImages() {
    return [
        [
            'src' => 'assets/images/iphone15pro.jpg',
            'alt' => 'iPhone 15 Pro - Latest Technology'
        ],
        [
            'src' => 'assets/images/galaxys24.jpg', 
            'alt' => 'Samsung Galaxy S24 - Premium Smartphone'
        ],
        [
            'src' => 'assets/images/dellxps13.jpg',
            'alt' => 'Dell XPS 13 - Ultra-portable Laptop'
        ]
    ];
}

$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = $conn->query($sql);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$bannerImages = getBannerImages();
?>

<div class="hero-banner-slider">
    <?php foreach ($bannerImages as $index => $image): ?>
        <img src="<?php echo htmlspecialchars($image['src']); ?>" 
             class="hero-banner-image <?php echo $index === 0 ? 'active' : ''; ?>" 
             alt="<?php echo htmlspecialchars($image['alt']); ?>">
    <?php endforeach; ?>
</div>

<div class="products-section">
    <h2>Our Latest Products</h2>
    <div class="products-carousel">
        <?php foreach ($products as $product): ?>
            <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
                <div class="quick-add-section">
                    <div class="quantity-controls">
                        <button type="button" class="quantity-btn" data-product-id="<?php echo $product['id']; ?>" data-change="-1">-</button>
                        <span class="quantity-display" id="quantity-<?php echo $product['id']; ?>">1</span>
                        <button type="button" class="quantity-btn" data-product-id="<?php echo $product['id']; ?>" data-change="1">+</button>
                    </div>
                    <button class="quick-add-btn" data-product-id="<?php echo $product['id']; ?>">
                        🛒 Add to Cart
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div> 