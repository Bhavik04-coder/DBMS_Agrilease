<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$error = '';
$success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_product'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $product_id = (int)$_POST['product_id'];
        

        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND listed_by = ?");
        $stmt->execute([$product_id, $_SESSION['user_id']]);
        $product = $stmt->fetch();
        
        if (!$product) {
            $error = 'Product not found or you do not have permission.';
        } else {

            $update_stmt = $pdo->prepare("UPDATE products SET status = 'available', availability = 'Available' WHERE id = ? AND listed_by = ?");
            $update_stmt->execute([$product_id, $_SESSION['user_id']]);
            
            $success = 'Product confirmed and is now available for booking!';
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_product'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $product_id = (int)$_POST['product_id'];
        

        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND listed_by = ?");
        $stmt->execute([$product_id, $_SESSION['user_id']]);
        $product = $stmt->fetch();
        
        if (!$product) {
            $error = 'Product not found or you do not have permission.';
        } else {

            $booking_stmt = $pdo->prepare("
                SELECT b.id, b.renter_id, u.full_name as renter_name 
                FROM bookings b 
                LEFT JOIN users u ON u.id = b.renter_id 
                WHERE b.product_id = ? AND b.status IN ('pending', 'confirmed')
            ");
            $booking_stmt->execute([$product_id]);
            $active_bookings = $booking_stmt->fetchAll();
            

            if (count($active_bookings) > 0) {
                $cancel_booking_stmt = $pdo->prepare("
                    UPDATE bookings 
                    SET status = 'cancelled' 
                    WHERE product_id = ? AND status IN ('pending', 'confirmed')
                ");
                $cancel_booking_stmt->execute([$product_id]);
                

                foreach ($active_bookings as $booking) {
                    sendNotification(
                        $booking['renter_id'], 
                        'Booking Cancelled', 
                        "Your booking for '{$product['title']}' has been cancelled by the owner.", 
                        'booking'
                    );
                }
            }
            

            $update_stmt = $pdo->prepare("UPDATE products SET status = 'maintenance', availability = 'Maintenance' WHERE id = ? AND listed_by = ?");
            $update_stmt->execute([$product_id, $_SESSION['user_id']]);
            
            $bookings_cancelled = count($active_bookings);
            if ($bookings_cancelled > 0) {
                $success = "Product cancelled successfully. {$bookings_cancelled} active booking(s) have been cancelled and renters have been notified.";
            } else {
                $success = 'Product cancelled and marked as unavailable.';
            }
        }
    }
}

$stmt = $pdo->prepare("
    SELECT p.*, 
    COUNT(CASE WHEN b.status IN ('pending', 'confirmed') THEN 1 END) as active_bookings_count
    FROM products p
    LEFT JOIN bookings b ON p.id = b.product_id
    WHERE p.listed_by = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<div class="container page">
  <div class="page-header">
    <div class="header-content">
      <div class="header-text">
        <h1 class="page-title">My Listings</h1>
        <p class="page-subtitle">Manage your rental products</p>
      </div>
      <a class="btn btn-primary btn-with-icon" href="add_product.php">
        <svg class="btn-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Add New Product
      </a>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error">
      <svg class="alert-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
      </svg>
      <?php echo $error; ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success">
      <svg class="alert-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L7.53 10.47a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.154-.114l4-5.5z" clip-rule="evenodd" />
      </svg>
      <?php echo $success; ?>
    </div>
  <?php endif; ?>

  <?php if (empty($products)): ?>
    <div class="empty-state">
      <div class="empty-icon">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14,2 14,8 20,8"></polyline>
          <line x1="16" y1="13" x2="8" y2="13"></line>
          <line x1="16" y1="17" x2="8" y2="17"></line>
          <polyline points="10,9 9,9 8,9"></polyline>
        </svg>
      </div>
      <h3>No listings yet</h3>
      <p>Start by adding your first product to rent out</p>
      <a class="btn btn-primary" href="add_product.php">Add Your First Product</a>
    </div>
  <?php else: ?>
    <div class="stats-bar">
      <div class="stat-item">
        <span class="stat-number"><?php echo count($products); ?></span>
        <span class="stat-label">Total Listings</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">₹<?php echo number_format(array_sum(array_column($products, 'price'))); ?></span>
        <span class="stat-label">Total Daily Value</span>
      </div>
    </div>

    <div class="products-grid">
      <?php foreach ($products as $p): ?>
        <div class="product-card">
          <div class="card-image">
            <img src="<?php echo htmlspecialchars($p['image_path'] ?: 'assets/images/Harvester2.jpg'); ?>" 
                 alt="<?php echo htmlspecialchars($p['title']); ?>" 
                 onerror="this.src='assets/images/Harvester2.jpg'">
            <div class="card-badge"><?php echo htmlspecialchars($p['category']); ?></div>
          </div>
          
          <div class="card-content">
            <h3 class="product-title"><?php echo htmlspecialchars($p['title']); ?></h3>
            
            <div class="product-meta">
              <div class="meta-item">
                <svg class="meta-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                </svg>
                <span class="price">₹<?php echo number_format((float)$p['price'], 2); ?>/day</span>
              </div>
              
              <div class="meta-item">
                <svg class="meta-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
                <span class="location"><?php echo htmlspecialchars($p['location'] ?: 'Not specified'); ?></span>
              </div>
              
              <?php if ($p['active_bookings_count'] > 0): ?>
              <div class="meta-item booking-count">
                <svg class="meta-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                  <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                </svg>
                <span class="booking-count-text"><?php echo $p['active_bookings_count']; ?> Active Booking<?php echo $p['active_bookings_count'] > 1 ? 's' : ''; ?></span>
              </div>
              <?php endif; ?>
            </div>
            
            <p class="product-description"><?php echo htmlspecialchars(truncateText($p['description'] ?? '', 100)); ?></p>
            
            <div class="product-status-badge status-<?php echo $p['status']; ?>">
              <?php echo ucfirst($p['status']); ?>
            </div>
            
            <div class="card-actions">
              <a class="btn btn-outline btn-sm btn-with-icon" href="product_detail.php?id=<?php echo (int)$p['id']; ?>">
                <svg class="btn-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                  <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                </svg>
                View
              </a>
              <a class="btn btn-outline btn-sm btn-with-icon" href="edit_product.php?id=<?php echo (int)$p['id']; ?>">
                <svg class="btn-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                </svg>
                Edit
              </a>
              
              <?php if ($p['status'] === 'maintenance'): ?>
                <form method="post" class="action-form">
                  <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                  <input type="hidden" name="confirm_product" value="1">
                  <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                  <button class="btn btn-success btn-sm btn-with-icon" type="submit">
                    <svg class="btn-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Confirm
                  </button>
                </form>
              <?php endif; ?>
              
              <?php if ($p['status'] === 'available'): ?>
                <form method="post" class="action-form" onsubmit="return confirm('Are you sure you want to cancel this product?\n\nThis will:\n- Mark the product as unavailable\n- Cancel all active bookings\n- Notify all affected renters\n\nThis action cannot be undone.');">
                  <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                  <input type="hidden" name="cancel_product" value="1">
                  <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                  <button class="btn btn-warning btn-sm btn-with-icon" type="submit">
                    <svg class="btn-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Cancel
                  </button>
                </form>
              <?php endif; ?>
              
              <form method="post" action="dashboard.php" class="delete-form" onsubmit="return confirm('Are you sure you want to permanently delete this listing? This action cannot be undone.');">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                <button class="btn btn-danger btn-sm btn-with-icon" type="submit" name="delete" value="1">
                  <svg class="btn-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  Delete
                </button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<style>
.page-header {
  margin-bottom: 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-text {
  flex: 1;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
}

.page-subtitle {
  color: #6b7280;
  margin: 0;
  font-size: 1.1rem;
}

.btn-with-icon {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 0.75rem 1.5rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-outline {
  background: transparent;
  color: #667eea;
  border: 2px solid #667eea;
  border-radius: 6px;
  padding: 0.5rem 1rem;
  text-decoration: none;
  transition: all 0.2s;
}

.btn-outline:hover {
  background: #667eea;
  color: white;
}

.btn-danger {
  background: #dc2626;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 0.5rem 1rem;
  transition: all 0.2s;
}

.btn-danger:hover {
  background: #b91c1c;
  transform: translateY(-1px);
}

.btn-success {
  background: #10b981;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 0.5rem 1rem;
  transition: all 0.2s;
}

.btn-success:hover {
  background: #059669;
  transform: translateY(-1px);
}

.btn-warning {
  background: #f59e0b;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 0.5rem 1rem;
  transition: all 0.2s;
}

.btn-warning:hover {
  background: #d97706;
  transform: translateY(-1px);
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
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

.stats-bar {
  display: flex;
  gap: 2rem;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-item {
  text-align: center;
}

.stat-number {
  display: block;
  font-size: 2rem;
  font-weight: 700;
  color: #667eea;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
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

.card-content {
  padding: 1.5rem;
}

.product-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 1rem 0;
  line-height: 1.4;
}

.product-meta {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
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

.booking-count {
  background: #fef3c7;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  border: 1px solid #fbbf24;
}

.booking-count .meta-icon {
  color: #d97706;
}

.booking-count-text {
  color: #92400e;
  font-weight: 600;
  font-size: 0.8rem;
}

.product-description {
  color: #6b7280;
  font-size: 0.875rem;
  line-height: 1.5;
  margin-bottom: 1.5rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-status-badge {
  margin-bottom: 1rem;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
  text-align: center;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-available {
  background: #d1fae5;
  color: #065f46;
}

.status-booked {
  background: #fef3c7;
  color: #92400e;
}

.status-maintenance {
  background: #fee2e2;
  color: #991b1b;
}

.card-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.action-form {
  display: inline-flex;
  margin: 0;
}

.delete-form {
  margin: 0;
}

.alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-weight: 500;
}

.alert-error {
  background-color: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.alert-success {
  background-color: #f0fdf4;
  color: #16a34a;
  border: 1px solid #bbf7d0;
}

.alert-icon {
  flex-shrink: 0;
}

.btn-icon {
  flex-shrink: 0;
}

@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    align-items: stretch;
    text-align: center;
  }
  
  .page-title {
    font-size: 2rem;
  }
  
  .stats-bar {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .products-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .card-actions {
    flex-direction: column;
  }
  
  .card-actions .btn {
    justify-content: center;
  }
}
</style>

<?php include 'includes/footer.php'; ?>
