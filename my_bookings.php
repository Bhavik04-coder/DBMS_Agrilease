<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
$stmt = $pdo->prepare("SELECT b.*, p.title as product_title, p.image_path FROM bookings b LEFT JOIN products p ON p.id = b.product_id WHERE b.renter_id = ? ORDER BY b.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<div class="container page">
  <div class="page-header">
    <div class="header-content">
      <div class="header-text">
        <h1 class="page-title">My Bookings</h1>
        <p class="page-subtitle">Manage your rental bookings and receipts</p>
      </div>
    </div>
  </div>

  <?php if (!$bookings): ?>
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
      <h3>No bookings yet</h3>
      <p>Start exploring available products to make your first booking</p>
      <a class="btn btn-primary" href="products.php">Browse Products</a>
    </div>
  <?php else: ?>
    <div class="stats-bar">
      <div class="stat-item">
        <span class="stat-number"><?php echo count($bookings); ?></span>
        <span class="stat-label">Total Bookings</span>
      </div>
      <div class="stat-item">
        <span class="stat-number"><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed')); ?></span>
        <span class="stat-label">Confirmed</span>
      </div>
      <div class="stat-item">
        <span class="stat-number"><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'pending')); ?></span>
        <span class="stat-label">Pending</span>
      </div>
    </div>

    <div class="bookings-list">
      <?php foreach ($bookings as $b): ?>
        <div class="booking-card">
          <div class="booking-image">
            <img src="<?php echo htmlspecialchars($b['image_path'] ?: 'assets/images/Harvester2.jpg'); ?>" 
                 alt="<?php echo htmlspecialchars($b['product_title']); ?>"
                 onerror="this.src='assets/images/Harvester2.jpg'">
          </div>
          
          <div class="booking-content">
            <div class="booking-header">
              <div class="booking-info">
                <span class="booking-id">Booking #<?php echo (int)$b['id']; ?></span>
                <h3 class="booking-title"><?php echo htmlspecialchars($b['product_title']); ?></h3>
              </div>
              <div class="booking-status status-<?php echo htmlspecialchars(strtolower($b['status'])); ?>">
                <?php echo htmlspecialchars($b['status']); ?>
              </div>
            </div>
            
            <div class="booking-meta">
              <div class="meta-item">
                <svg class="meta-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                </svg>
                <span>Booked on <?php echo date('M j, Y', strtotime($b['created_at'])); ?></span>
              </div>
              
              <div class="meta-item">
                <svg class="meta-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                <span>At <?php echo date('g:i A', strtotime($b['created_at'])); ?></span>
              </div>
            </div>
            
            <?php if (!empty($b['start_date']) && !empty($b['end_date'])): ?>
              <div class="booking-dates">
                <div class="date-range">
                  <span class="date-label">Rental Period:</span>
                  <span class="date-value">
                    <?php echo date('M j, Y', strtotime($b['start_date'])); ?> 
                    - 
                    <?php echo date('M j, Y', strtotime($b['end_date'])); ?>
                  </span>
                </div>
              </div>
            <?php endif; ?>
            
            <div class="booking-actions">
              <a class="btn btn-primary btn-with-icon" href="receipt.php?id=<?php echo (int)$b['id']; ?>">
                <svg class="btn-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                </svg>
                View Receipt
              </a>
              
              <?php if ($b['status'] === 'pending'): ?>
                <button class="btn btn-outline btn-with-icon" onclick="cancelBooking(<?php echo (int)$b['id']; ?>)">
                  <svg class="btn-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                  Cancel
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
function cancelBooking(bookingId) {
  if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
    // You can implement cancellation logic here
    alert('Cancellation feature to be implemented');
  }
}
</script>

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

.bookings-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.booking-card {
  display: flex;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
}

.booking-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.booking-image {
  width: 200px;
  flex-shrink: 0;
}

.booking-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.booking-content {
  flex: 1;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.booking-info {
  flex: 1;
}

.booking-id {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.booking-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0.25rem 0 0 0;
  line-height: 1.4;
}

.booking-status {
  padding: 0.375rem 1rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.status-confirmed {
  background: #d1fae5;
  color: #065f46;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-cancelled {
  background: #fee2e2;
  color: #991b1b;
}

.status-completed {
  background: #e0e7ff;
  color: #3730a3;
}

.booking-meta {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
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

.booking-dates {
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 8px;
  border-left: 4px solid #667eea;
}

.date-range {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.date-label {
  font-weight: 600;
  color: #374151;
}

.date-value {
  color: #667eea;
  font-weight: 500;
}

.booking-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: auto;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-outline {
  background: transparent;
  color: #6b7280;
  border: 2px solid #e5e7eb;
}

.btn-outline:hover {
  background: #f3f4f6;
  border-color: #d1d5db;
}

.btn-with-icon {
  padding: 0.5rem 1rem;
}

.btn-icon {
  flex-shrink: 0;
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
  .booking-card {
    flex-direction: column;
  }
  
  .booking-image {
    width: 100%;
    height: 200px;
  }
  
  .booking-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .stats-bar {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .booking-actions {
    flex-direction: column;
  }
  
  .page-title {
    font-size: 2rem;
  }
}
</style>

<?php include 'includes/footer.php'; ?>