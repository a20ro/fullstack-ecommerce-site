<?php
require_once __DIR__ . '/../config/database.php';

// Get product ID from URL parameter
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    // Redirect to home if product not found
    header("Location: index.php?page=home");
    exit();
}
?>


<div class="item-container">
    <div class="item-image-section">
        <img src="<?php echo htmlspecialchars($product['image']); ?>" 
             alt="<?php echo htmlspecialchars($product['name']); ?>" 
             class="item-image">
    </div>
    
    <div class="item-details-section">
        <h1 class="item-title"><?php echo htmlspecialchars($product['name']); ?></h1>
        
        <div class="item-price">$<?php echo number_format($product['price'], 2); ?></div>
        
        <div class="item-description">
            <h3>Description</h3>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
        </div>
        
        <div class="add-to-cart-section">
            <div class="quantity-selector">
                <label for="quantity">Quantity:</label>
                <div class="quantity-controls">
                    <button type="button" class="quantity-btn" data-change="-1">-</button>
                    <input type="number" id="quantity" value="1" min="1" max="99" class="quantity-input">
                    <button type="button" class="quantity-btn" data-change="1">+</button>
                </div>
            </div>
            
            <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                🛒 Add to Cart
            </button>
        </div>
        
        <div class="item-actions">
            <a href="#" class="back-btn">← Back to Products</a>
        </div>
    </div>
</div>

