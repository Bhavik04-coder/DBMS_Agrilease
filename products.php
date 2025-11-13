<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';


$search = sanitizeInput($_GET['search'] ?? '');
$category = sanitizeInput($_GET['category'] ?? '');
$location = sanitizeInput($_GET['location'] ?? '');
$min_price = (float)($_GET['min_price'] ?? 0);
$max_price = (float)($_GET['max_price'] ?? 0);


$where_conditions = ["p.status = 'available'"];
$params = [];

if ($search) {
    $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $where_conditions[] = "p.category = ?";
    $params[] = $category;
}

if ($location) {
    $where_conditions[] = "p.location LIKE ?";
    $params[] = "%$location%";
}

if ($min_price > 0) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $min_price;
}

if ($max_price > 0) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $max_price;
}

$where_clause = implode(' AND ', $where_conditions);

$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.full_name,
    CASE WHEN p.listed_by = ? THEN 1 ELSE 0 END as is_own_product
    FROM products p 
    LEFT JOIN users u ON u.id = p.listed_by 
    WHERE $where_clause
    ORDER BY p.created_at DESC
");
$params_with_user = array_merge([$_SESSION['user_id']], $params);
$stmt->execute($params_with_user);
$products = $stmt->fetchAll();


$cat_stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category");
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<?php include 'includes/header.php'; ?>

<div class="container page">
  <div class="page-header">
    <div class="header-content">
      <div class="header-text">
        <h1 class="page-title">Browse Equipment</h1>
        <p class="page-subtitle">Find the perfect agricultural equipment for your needs</p>
      </div>
    </div>
  </div>

  <!-- Search and Filters -->
  <div class="filters-section">
    <form method="get" class="filters-form">
      <div class="filters-grid">
        <div class="filter-group">
          <input type="text" name="search" placeholder="Search equipment..." 
                 value="<?php echo htmlspecialchars($search); ?>" class="filter-input">
        </div>
        
        <div class="filter-group">
          <select name="category" class="filter-select">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo htmlspecialchars($cat); ?>" 
                      <?php echo $category === $cat ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="filter-group">
          <input type="text" name="location" placeholder="Location..." 
                 value="<?php echo htmlspecialchars($location); ?>" class="filter-input">
        </div>
        
        <div class="filter-group price-range">
          <input type="number" name="min_price" placeholder="Min ₹" 
                 value="<?php echo $min_price > 0 ? $min_price : ''; ?>" class="filter-input price-input">
          <span class="price-separator">-</span>
          <input type="number" name="max_price" placeholder="Max ₹" 
                 value="<?php echo $max_price > 0 ? $max_price : ''; ?>" class="filter-input price-input">
        </div>
        
        <div class="filter-actions">
          <button type="submit" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
            </svg>
            Search
          </button>
          <a href="products.php" class="btn btn-outline">Clear</a>
        </div>
      </div>
    </form>
  </div>

  <!-- Results -->
  <div class="results-header">
    <h2>Available Equipment (<?php echo count($products); ?>)</h2>
    <?php if (count($products) === 0): ?>
      <p style="color: #6b7280; margin-top: 0.5rem;">
        <?php 

        $total_check = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $available_check = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'available'")->fetchColumn();
        echo "Total products in database: $total_check | Available: $available_check";
        ?>
      </p>
    <?php endif; ?>
  </div>

  <?php if (empty($products)): ?>
    <div class="empty-state">
      <div class="empty-icon">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="M21 21l-4.35-4.35"></path>
        </svg>
      </div>
      <h3>No equipment found</h3>
      <p>Try adjusting your search criteria or browse all available equipment</p>
      <a class="btn btn-primary" href="products.php">View All Equipment</a>
    </div>
  <?php else: ?>
    <div class="products-grid">
      <?php foreach ($products as $p): ?>
        <div class="product-card">
          <div class="card-image">
            <img src="<?php echo htmlspecialchars($p['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" 
                 alt="<?php echo htmlspecialchars($p['title']); ?>" 
                 onerror="this.src='assets/images/placeholder.jpg'">
            <div class="card-badge"><?php echo htmlspecialchars($p['category']); ?></div>
            <?php if ($p['is_own_product']): ?>
              <div class="own-product-badge">Your Product</div>
            <?php endif; ?>
            <div class="card-overlay">
              <div class="overlay-actions">
                <a href="product_detail.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-light btn-sm">
                  <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                  </svg>
                  View Details
                </a>
                <a href="product_detail.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-primary btn-sm">
                  <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                  </svg>
                  Book Now
                </a>
              </div>
            </div>
          </div>
          
          <div class="card-content">
            <h3 class="product-title"><?php echo htmlspecialchars($p['title']); ?></h3>
            
            <div class="product-meta">
              <div class="meta-item">
                <svg class="meta-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                </svg>
                <span class="price"><?php echo formatPrice($p['price']); ?>/day</span>
              </div>
              
              <div class="meta-item">
                <svg class="meta-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
                <span class="location"><?php echo htmlspecialchars($p['location'] ?: 'Not specified'); ?></span>
              </div>
              
              <div class="meta-item">
                <svg class="meta-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
                <span class="owner"><?php echo htmlspecialchars($p['full_name'] ?: $p['username']); ?></span>
              </div>
            </div>
            
            <p class="product-description"><?php echo htmlspecialchars(truncateText($p['description'] ?? '', 120)); ?></p>
            
            <div class="card-actions">
              <a class="btn btn-outline btn-sm" href="product_detail.php?id=<?php echo (int)$p['id']; ?>">
                View Details
              </a>
              <?php if ($p['is_own_product']): ?>
                <a href="edit_product.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-success btn-sm">
                  <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                  </svg>
                  Edit
                </a>
              <?php else: ?>
                <a href="product_detail.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-primary btn-sm">Book Now</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<style>
.filters-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.filters-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1.5fr auto;
  gap: 1rem;
  align-items: end;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.price-range {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.price-input {
  flex: 1;
}

.price-separator {
  color: #6b7280;
  font-weight: 500;
}

.filter-input, .filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.filter-input:focus, .filter-select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filter-actions {
  display: flex;
  gap: 0.5rem;
}

.results-header {
  margin-bottom: 1.5rem;
}

.results-header h2 {
  font-size: 1.5rem;
  color: #1f2937;
  margin: 0;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.product-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border: 1px solid #e5e7eb;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.card-image {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.product-card:hover .card-image img {
  transform: scale(1.05);
}

.card-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  background: rgba(102, 126, 234, 0.9);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  backdrop-filter: blur(4px);
}

.own-product-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: rgba(16, 185, 129, 0.9);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  backdrop-filter: blur(4px);
}

.card-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s;
}

.product-card:hover .card-overlay {
  opacity: 1;
}

.overlay-actions {
  display: flex;
  gap: 0.5rem;
}

.card-content {
  padding: 1.25rem;
}

.product-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 0.75rem 0;
  line-height: 1.4;
}

.product-meta {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6b7280;
  font-size: 0.875rem;
}

.meta-icon {
  flex-shrink: 0;
  color: #9ca3af;
}

.price {
  color: #059669;
  font-weight: 600;
}

.product-description {
  color: #6b7280;
  font-size: 0.875rem;
  line-height: 1.5;
  margin-bottom: 1rem;
}

.card-actions {
  display: flex;
  gap: 0.5rem;
}

.book-form {
  flex: 1;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5a67d8;
  transform: translateY(-1px);
}

.btn-outline {
  background: transparent;
  color: #667eea;
  border: 1px solid #667eea;
}

.btn-outline:hover {
  background: #667eea;
  color: white;
}

.btn-light {
  background: rgba(255, 255, 255, 0.9);
  color: #1f2937;
}

.btn-light:hover {
  background: white;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
  transform: translateY(-1px);
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.8rem;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #6b7280;
}

.empty-icon {
  margin-bottom: 1.5rem;
  color: #d1d5db;
}

.empty-state h3 {
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-state p {
  margin-bottom: 2rem;
}

@media (max-width: 768px) {
  .filters-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .filter-actions {
    justify-content: stretch;
  }
  
  .filter-actions .btn {
    flex: 1;
  }
  
  .products-grid {
    grid-template-columns: 1fr;
  }
  
  .card-actions {
    flex-direction: column;
  }
}
</style>

<?php include 'includes/footer.php'; ?>
