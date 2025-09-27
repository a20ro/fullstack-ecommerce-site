<?php
require_once __DIR__ . '/../config/database.php';

// Handle search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$params = [];
$types = '';

if (!empty($search)) {
    $where_clause = "WHERE name LIKE ? OR description LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param];
    $types = 'ss';
}

// Fetch products from database
$sql = "SELECT * FROM products $where_clause ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<div class="page-content">
    <h1>Our Products</h1>
    


    <!-- Search Bar -->
    <div class="search-section">
        <form method="GET" action="" class="search-form" id="search-form">
            <input type="hidden" name="page" value="products">
            <div class="search-container">
                <input type="text" 
                       name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search products by name or description..." 
                       class="search-input"
                       id="search-input">
                <button type="submit" class="search-btn">🔍 Search</button>
                <?php if (!empty($search)): ?>
                    <a href="#" class="clear-search-btn" id="clear-search-btn">Clear</a>
                <?php endif; ?>
            </div>
        </form>
        
        <?php if (!empty($search)): ?>
            <div class="search-results-info">
                <p>Search results for: "<strong><?php echo htmlspecialchars($search); ?></strong>" 
                   (<?php echo count($products); ?> product<?php echo count($products) !== 1 ? 's' : ''; ?> found)</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (empty($products)): ?>
        <div class="no-products">
            <div class="no-products-icon">📦</div>
            <h3>No products found</h3>
            <p>
                <?php if (!empty($search)): ?>
                    No products match your search criteria. Try different keywords or 
                    <a href="?page=products">browse all products</a>.
                <?php else: ?>
                    No products are currently available. Please check back later.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div class="product-placeholder">📱</div>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                    <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                    <div class="product-actions">
                        <a href="#" class="view-details-btn" data-product-id="<?php echo $product['id']; ?>">View Details</a>
                        <button class="add-to-cart" data-product-id="<?php echo $product['id']; ?>">Add to Cart</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.products-intro {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin: 30px 0;
    text-align: center;
}

.products-intro p {
    font-size: 1.2em;
    line-height: 1.8;
    max-width: 800px;
    margin: 0 auto;
}

/* Search Section Styles */
.search-section {
    margin: 30px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #e9ecef;
}

.search-form {
    margin-bottom: 15px;
}

.search-container {
    display: flex;
    gap: 10px;
    align-items: center;
    max-width: 800px;
    margin: 0 auto;
}

.search-input {
    flex: 1;
    padding: 12px 20px;
    border: 2px solid #ddd;
    border-radius: 25px;
    font-size: 16px;
    outline: none;
    transition: border-color 0.3s ease;
    color: #000;
    background-color: #fff;
}

.search-input:focus {
    border-color: #3498db;
}

.search-input::placeholder {
    color: #999;
}

.search-btn {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 25px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-btn:hover {
    background: linear-gradient(135deg, #2980b9, #1f5f8b);
    transform: translateY(-2px);
}

.clear-search-btn {
    background: #e74c3c;
    color: white;
    text-decoration: none;
    padding: 12px 20px;
    border-radius: 25px;
    font-weight: bold;
    transition: all 0.3s ease;
}

.clear-search-btn:hover {
    background: #c0392b;
    transform: translateY(-2px);
}

.search-results-info {
    text-align: center;
    color: #666;
    font-style: italic;
}

/* No Products Styles */
.no-products {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-products-icon {
    font-size: 4em;
    margin-bottom: 20px;
}

.no-products h3 {
    color: #2c3e50;
    margin-bottom: 15px;
}

.no-products a {
    color: #3498db;
    text-decoration: none;
}

.no-products a:hover {
    text-decoration: underline;
}

/* Products Grid */
.products-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    margin: 40px 0;
    justify-content: flex-start;
}

.product-card {
    flex: 0 0 300px;
    max-width: 300px;
}

.product-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-align: center;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    margin-bottom: 20px;
}

.product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #3498db;
}

.product-image {
    margin-bottom: 20px;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 8px;
}

.product-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 8px;
}

.product-placeholder {
    font-size: 4em;
    color: #bdc3c7;
}

.product-card h3 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-size: 1.4em;
    font-weight: 600;
}

.product-description {
    color: #7f8c8d;
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 0.95em;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-price {
    font-size: 1.8em;
    font-weight: bold;
    color: #3498db;
    margin-bottom: 20px;
}

.product-actions {
    display: flex;
    gap: 10px;
    flex-direction: column;
}

.view-details-btn {
    background: linear-gradient(135deg, #27ae60, #229954);
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: bold;
    transition: all 0.3s ease;
    display: inline-block;
    width: 100%;
    text-align: center;
}

.view-details-btn:hover {
    background: linear-gradient(135deg, #229954, #1e8449);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
}

.add-to-cart {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: bold;
    font-size: 1em;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.add-to-cart:hover {
    background: linear-gradient(135deg, #2980b9, #1f5f8b);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

@media (max-width: 768px) {
    .products-grid {
        justify-content: center;
    }
    
    .product-card {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .search-container {
        flex-direction: column;
    }
    
    .search-input,
    .search-btn,
    .clear-search-btn {
        width: 100%;
    }
}
</style>

 