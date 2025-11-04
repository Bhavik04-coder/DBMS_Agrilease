<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$id = (int)$_GET['id'];

// Fetch product with owner details
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.full_name, u.email, u.phone, u.address 
    FROM products p 
    LEFT JOIN users u ON u.id = p.listed_by 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

// Check if user owns this product
$is_owner = (int)$product['listed_by'] === (int)$_SESSION['user_id'];

// Get related products
$related_stmt = $pdo->prepare("
    SELECT p.*, u.username, u.full_name 
    FROM products p 
    LEFT JOIN users u ON u.id = p.listed_by 
    WHERE p.category = ? AND p.id != ? AND p.status = 'available' 
    LIMIT 3
");
$related_stmt->execute([$product['category'], $id]);
$related_products = $related_stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<div class="container page">
  <div class="breadcrumb">
    <a href="products.php">← Back to Products</a>
  </div>

  <div class="product-detail">
    <!-- Product Images -->
    <div class="product-images">
      <div class="main-image">
        <img src="<?php echo htmlspecialchars($product['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" 
             alt="<?php echo htmlspecialchars($product['title']); ?>"
             onerror="this.src='assets/images/placeholder.jpg'">
      </div>
    </div>

    <!-- Product Info -->
    <div class="product-info">
      <div class="product-header">
        <div class="category-badge"><?php echo htmlspecialchars($product['category']); ?></div>
        <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>
        <div class="product-price"><?php echo formatPrice($product['price']); ?><span>/day</span></div>
      </div>

      <div class="product-meta">
        <div class="meta-item">
          <svg class="meta-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
          </svg>
          <span><?php echo htmlspecialchars($product['location'] ?: 'Location not specified'); ?></span>
        </div>
        
        <div class="meta-item">
          <svg class="meta-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
          </svg>
          <span>Listed <?php echo date('M j, Y', strtotime($product['created_at'])); ?></span>
        </div>
        
        <div class="meta-item">
          <svg class="meta-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="status-<?php echo strtolower($product['availability']); ?>">
            <?php echo htmlspecialchars($product['availability']); ?>
          </span>
        </div>
      </div>

      <div class="product-description">
        <h3>Description</h3>
        <p><?php echo nl2br(htmlspecialchars($product['description'] ?: 'No description available.')); ?></p>
      </div>

      <?php if (!$is_owner): ?>
        <div class="booking-section">
          <h3>Book this Equipment</h3>
          <form method="post" action="book.php" class="booking-form">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
            
            <div class="form-grid">
              <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" required 
                       min="<?php echo date('Y-m-d'); ?>" class="form-input">
              </div>
              
              <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" required 
                       min="<?php echo date('Y-m-d'); ?>" class="form-input">
              </div>
            </div>
            
            <div class="form-group">
              <label for="notes">Additional Notes (Optional)</label>
              <textarea id="notes" name="notes" rows="3" class="form-textarea" 
                        placeholder="Any special requirements or notes..."></textarea>
            </div>
            
            <div class="booking-summary">
              <div class="summary-row">
                <span>Daily Rate:</span>
                <span class="daily-rate"><?php echo formatPrice($product['price']); ?></span>
              </div>
              <div class="summary-row">
                <span>Duration:</span>
                <span class="duration">0 days</span>
              </div>
              <div class="summary-row total">
                <span>Total Amount:</span>
                <span class="total-amount">₹0.00</span>
              </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-large">
              <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
              </svg>
              Book Now
            </button>
          </form>
        </div>
      <?php else: ?>
        <div class="owner-actions">
          <h3>Manage Your Listing</h3>
          <div class="action-buttons">
            <a href="edit_product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-outline">
              <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
              </svg>
              Edit Listing
            </a>
            <a href="my_products.php" class="btn btn-primary">View All My Products</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Owner Information -->
  <div class="owner-section">
    <h3>Equipment Owner</h3>
    <div class="owner-card">
      <div class="owner-avatar">
        <svg width="48" height="48" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
        </svg>
      </div>
      <div class="owner-info">
        <h4><?php echo htmlspecialchars($product['full_name'] ?: $product['username']); ?></h4>
        <p class="owner-contact">
          <?php if ($product['email']): ?>
            <span>📧 <?php echo htmlspecialchars($product['email']); ?></span>
          <?php endif; ?>
          <?php if ($product['phone']): ?>
            <span>📞 <?php echo htmlspecialchars($product['phone']); ?></span>
          <?php endif; ?>
        </p>
        <?php if ($product['address']): ?>
          <p class="owner-address">📍 <?php echo htmlspecialchars($product['address']); ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Map Section -->
  <?php if ($product['lat'] && $product['lng']): ?>
    <div class="map-section">
      <h3>Location</h3>
      <div class="map-container">
        <div id="map"></div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Related Products -->
  <?php if (!empty($related_products)): ?>
    <div class="related-section">
      <h3>Similar Equipment</h3>
      <div class="related-grid">
        <?php foreach ($related_products as $rp): ?>
          <div class="related-card">
            <div class="related-image">
              <img src="<?php echo htmlspecialchars($rp['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" 
                   alt="<?php echo htmlspecialchars($rp['title']); ?>"
                   onerror="this.src='assets/images/placeholder.jpg'">
            </div>
            <div class="related-content">
              <h4><?php echo htmlspecialchars($rp['title']); ?></h4>
              <p class="related-price"><?php echo formatPrice($rp['price']); ?>/day</p>
              <a href="product_detail.php?id=<?php echo (int)$rp['id']; ?>" class="btn btn-outline btn-sm">View Details</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
.breadcrumb {
  margin-bottom: 1.5rem;
}

.breadcrumb a {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
}

.breadcrumb a:hover {
  text-decoration: underline;
}

.product-detail {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  margin-bottom: 3rem;
}

.product-images {
  position: sticky;
  top: 2rem;
  height: fit-content;
}

.main-image {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.main-image img {
  width: 100%;
  height: 400px;
  object-fit: cover;
  display: block;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.product-header {
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.category-badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  background: #667eea;
  color: white;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 1rem;
}

.product-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 1rem 0;
  line-height: 1.2;
}

.product-price {
  font-size: 2rem;
  font-weight: 800;
  color: #059669;
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
}

.product-price span {
  font-size: 1rem;
  font-weight: 500;
  color: #6b7280;
}

.product-meta {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1rem;
  color: #374151;
}

.meta-icon {
  flex-shrink: 0;
  color: #9ca3af;
}

.status-available {
  color: #059669;
  font-weight: 600;
}

.status-rented {
  color: #dc2626;
  font-weight: 600;
}

.product-description h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.75rem;
}

.product-description p {
  color: #6b7280;
  line-height: 1.6;
  margin: 0;
}

.booking-section, .owner-actions {
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
}

.booking-section h3, .owner-actions h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
}

.form-input, .form-textarea {
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.2s;
}

.form-input:focus, .form-textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.booking-summary {
  background: white;
  border-radius: 8px;
  padding: 1rem;
  margin: 1rem 0;
  border: 1px solid #e5e7eb;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f3f4f6;
}

.summary-row:last-child {
  border-bottom: none;
}

.summary-row.total {
  font-weight: 700;
  font-size: 1.1rem;
  color: #1f2937;
  border-top: 2px solid #e5e7eb;
  margin-top: 0.5rem;
  padding-top: 1rem;
}

.action-buttons {
  display: flex;
  gap: 1rem;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
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
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
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

.btn-large {
  padding: 1rem 2rem;
  font-size: 1.1rem;
  width: 100%;
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

.owner-section, .map-section, .related-section {
  margin-bottom: 3rem;
}

.owner-section h3, .map-section h3, .related-section h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
}

.owner-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.owner-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 64px;
  background: #f3f4f6;
  border-radius: 50%;
  color: #9ca3af;
  flex-shrink: 0;
}

.owner-info h4 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 0.5rem 0;
}

.owner-contact {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.9rem;
  color: #6b7280;
  margin: 0 0 0.5rem 0;
}

.owner-address {
  font-size: 0.9rem;
  color: #6b7280;
  margin: 0;
}

.map-container {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

#map {
  height: 300px;
  width: 100%;
}

.related-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.related-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
}

.related-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.related-image {
  height: 150px;
  overflow: hidden;
}

.related-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.related-card:hover .related-image img {
  transform: scale(1.05);
}

.related-content {
  padding: 1rem;
}

.related-content h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 0.5rem 0;
  line-height: 1.3;
}

.related-price {
  font-size: 1rem;
  font-weight: 700;
  color: #059669;
  margin: 0 0 1rem 0;
}

@media (max-width: 768px) {
  .product-detail {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
  
  .product-images {
    position: static;
  }
  
  .main-image img {
    height: 300px;
  }
  
  .product-title {
    font-size: 1.5rem;
  }
  
  .product-price {
    font-size: 1.5rem;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .owner-card {
    flex-direction: column;
    text-align: center;
  }
  
  .related-grid {
    grid-template-columns: 1fr;
  }
}
</style>

<script>
// Calculate booking total
document.addEventListener('DOMContentLoaded', function() {
  const startDateInput = document.getElementById('start_date');
  const endDateInput = document.getElementById('end_date');
  const durationSpan = document.querySelector('.duration');
  const totalAmountSpan = document.querySelector('.total-amount');
  const dailyRate = <?php echo (float)$product['price']; ?>;
  
  function calculateTotal() {
    const startDate = new Date(startDateInput.value);
    const endDate = new Date(endDateInput.value);
    
    if (startDate && endDate && endDate >= startDate) {
      const timeDiff = endDate.getTime() - startDate.getTime();
      const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // Include both start and end dates
      const total = daysDiff * dailyRate;
      
      durationSpan.textContent = daysDiff + ' day' + (daysDiff > 1 ? 's' : '');
      totalAmountSpan.textContent = '₹' + total.toFixed(2);
    } else {
      durationSpan.textContent = '0 days';
      totalAmountSpan.textContent = '₹0.00';
    }
  }
  
  startDateInput.addEventListener('change', calculateTotal);
  endDateInput.addEventListener('change', calculateTotal);
  
  // Set minimum end date when start date changes
  startDateInput.addEventListener('change', function() {
    endDateInput.min = this.value;
    if (endDateInput.value && endDateInput.value < this.value) {
      endDateInput.value = this.value;
    }
    calculateTotal();
  });
});

// Initialize map if coordinates are available
<?php if ($product['lat'] && $product['lng']): ?>
(function() {
  const lat = <?php echo (float)$product['lat']; ?>;
  const lng = <?php echo (float)$product['lng']; ?>;
  
  const map = L.map('map').setView([lat, lng], 13);
  
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);
  
  const marker = L.marker([lat, lng]).addTo(map);
  marker.bindPopup('<strong><?php echo htmlspecialchars($product['title']); ?></strong><br><?php echo htmlspecialchars($product['location']); ?>');
})();
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>