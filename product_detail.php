<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$product_id = (int)($_GET['id'] ?? 0);

if (!$product_id) {
    header('Location: products.php');
    exit;
}

// Fetch product details
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.full_name, u.email, u.phone 
    FROM products p 
    LEFT JOIN users u ON u.id = p.listed_by 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

// Fetch reviews for this product
$reviews_stmt = $pdo->prepare("
    SELECT r.*, u.username, u.full_name 
    FROM reviews r 
    LEFT JOIN users u ON u.id = r.reviewer_id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$reviews_stmt->execute([$product_id]);
$reviews = $reviews_stmt->fetchAll();

// Calculate average rating
$avg_rating = 0;
if (count($reviews) > 0) {
    $total_rating = array_sum(array_column($reviews, 'rating'));
    $avg_rating = $total_rating / count($reviews);
}

// Handle review submission
$review_error = '';
$review_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $review_error = 'Invalid session. Please try again.';
    } else {
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = sanitizeInput($_POST['comment'] ?? '');
        
        if ($rating < 1 || $rating > 5) {
            $review_error = 'Please select a rating between 1 and 5 stars.';
        } elseif (empty($comment)) {
            $review_error = 'Please write a review comment.';
        } else {
            // Check if user already reviewed this product
            $check_stmt = $pdo->prepare("SELECT id FROM reviews WHERE product_id = ? AND reviewer_id = ?");
            $check_stmt->execute([$product_id, $_SESSION['user_id']]);
            
            if ($check_stmt->fetch()) {
                $review_error = 'You have already reviewed this product.';
            } else {
                $insert_stmt = $pdo->prepare("
                    INSERT INTO reviews (product_id, reviewer_id, reviewed_id, rating, comment, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $insert_stmt->execute([$product_id, $_SESSION['user_id'], $product['listed_by'], $rating, $comment]);
                $review_success = 'Review submitted successfully!';
                
                // Refresh reviews
                $reviews_stmt->execute([$product_id]);
                $reviews = $reviews_stmt->fetchAll();
                
                // Recalculate average
                if (count($reviews) > 0) {
                    $total_rating = array_sum(array_column($reviews, 'rating'));
                    $avg_rating = $total_rating / count($reviews);
                }
            }
        }
    }
}

$is_owner = (int)$product['listed_by'] === (int)$_SESSION['user_id'];
?>
<?php include 'includes/header.php'; ?>

<div class="container page">
  <div class="product-detail-container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
      <a href="dashboard.php">Dashboard</a>
      <span>/</span>
      <a href="products.php">Products</a>
      <span>/</span>
      <span><?php echo htmlspecialchars($product['title']); ?></span>
    </div>

    <div class="product-detail-grid">
      <!-- Left Column: Image and Gallery -->
      <div class="product-detail-left">
        <div class="product-detail-image">
          <?php if ($product['image_path']): ?>
            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
          <?php else: ?>
            <div class="product-image-placeholder-large">
              <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
              </svg>
              <span>No Image Available</span>
            </div>
          <?php endif; ?>
        </div>

        <!-- Product Info Cards -->
        <div class="product-info-cards">
          <div class="info-card">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <div>
              <div class="info-label">Available</div>
              <div class="info-value"><?php echo htmlspecialchars($product['availability'] ?? 'Available'); ?></div>
            </div>
          </div>

          <div class="info-card">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
            <div>
              <div class="info-label">Location</div>
              <div class="info-value"><?php echo htmlspecialchars($product['location'] ?: 'Not specified'); ?></div>
            </div>
          </div>

          <div class="info-card">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <div>
              <div class="info-label">Listed</div>
              <div class="info-value"><?php echo date('M j, Y', strtotime($product['created_at'])); ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Details and Booking -->
      <div class="product-detail-right">
        <div class="product-detail-header">
          <div class="category-badge"><?php echo htmlspecialchars($product['category'] ?? 'General'); ?></div>
          <h1 class="product-detail-title"><?php echo htmlspecialchars($product['title']); ?></h1>
          
          <!-- Rating Display -->
          <div class="rating-display">
            <div class="stars">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <svg class="star <?php echo $i <= round($avg_rating) ? 'filled' : ''; ?>" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
              <?php endfor; ?>
            </div>
            <span class="rating-text">
              <?php echo number_format($avg_rating, 1); ?> 
              (<?php echo count($reviews); ?> <?php echo count($reviews) === 1 ? 'review' : 'reviews'; ?>)
            </span>
          </div>

          <div class="product-price-large">
            ₹<?php echo number_format((float)$product['price'], 2); ?>
            <span class="price-period">/day</span>
          </div>
        </div>

        <div class="product-detail-description">
          <h3>Description</h3>
          <p><?php echo nl2br(htmlspecialchars($product['description'] ?: 'No description available.')); ?></p>
        </div>

        <!-- Owner Info -->
        <div class="owner-info">
          <h3>Listed By</h3>
          <div class="owner-card">
            <div class="owner-avatar">
              <?php echo strtoupper(substr($product['username'], 0, 1)); ?>
            </div>
            <div class="owner-details">
              <div class="owner-name"><?php echo htmlspecialchars($product['full_name'] ?: $product['username']); ?></div>
              <div class="owner-username">@<?php echo htmlspecialchars($product['username']); ?></div>
            </div>
          </div>
        </div>

        <!-- Booking Section -->
        <?php if (!$is_owner): ?>
        <div class="booking-section">
          <h3>Book This Equipment</h3>
          
          <?php if ($product['status'] !== 'available'): ?>
            <div class="unavailable-notice">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
              </svg>
              <p>This equipment is currently <strong><?php echo htmlspecialchars($product['status']); ?></strong> and not available for booking.</p>
            </div>
          <?php else: ?>
          
          <form method="post" action="book.php" class="booking-form-detail" id="bookingForm">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            
            <div class="form-row two">
              <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" required min="<?php echo date('Y-m-d'); ?>" onchange="calculateTotal()">
              </div>
              <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" required min="<?php echo date('Y-m-d'); ?>" onchange="calculateTotal()">
              </div>
            </div>

            <div id="priceCalculation" class="price-calculation" style="display:none;">
              <div class="calc-row">
                <span>Duration:</span>
                <span id="duration">0 days</span>
              </div>
              <div class="calc-row">
                <span>Daily Rate:</span>
                <span>₹<?php echo number_format((float)$product['price'], 2); ?></span>
              </div>
              <div class="calc-row total">
                <span>Estimated Total:</span>
                <span id="totalPrice">₹0.00</span>
              </div>
              <div class="calc-note">*Excluding taxes and fees</div>
            </div>

            <div class="form-group">
              <label for="notes">Message (Optional)</label>
              <textarea id="notes" name="notes" rows="3" placeholder="Any special requirements or questions..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              Book Now
            </button>
          </form>
          
          <script>
          function calculateTotal() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const pricePerDay = <?php echo (float)$product['price']; ?>;
            
            if (startDate && endDate) {
              const start = new Date(startDate);
              const end = new Date(endDate);
              const diffTime = Math.abs(end - start);
              const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // Include both days
              
              if (diffDays > 0) {
                const total = diffDays * pricePerDay;
                document.getElementById('duration').textContent = diffDays + ' day' + (diffDays > 1 ? 's' : '');
                document.getElementById('totalPrice').textContent = '₹' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                document.getElementById('priceCalculation').style.display = 'block';
              }
            }
          }
          </script>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="owner-notice">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
          </svg>
          <p>This is your product. You can <a href="edit_product.php?id=<?php echo $product_id; ?>">edit</a> or manage it from your dashboard.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Reviews Section -->
    <div class="reviews-section">
      <div class="reviews-header">
        <h2>Customer Reviews</h2>
        <div class="reviews-summary">
          <div class="reviews-average">
            <div class="average-number"><?php echo number_format($avg_rating, 1); ?></div>
            <div class="stars-large">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <svg class="star <?php echo $i <= round($avg_rating) ? 'filled' : ''; ?>" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
              <?php endfor; ?>
            </div>
            <div class="reviews-count"><?php echo count($reviews); ?> reviews</div>
          </div>
        </div>
      </div>

      <!-- Write Review Form -->
      <?php if (!$is_owner): ?>
      <div class="write-review-section">
        <h3>Write a Review</h3>
        
        <?php if ($review_error): ?>
          <div class="error-message"><?php echo $review_error; ?></div>
        <?php endif; ?>
        
        <?php if ($review_success): ?>
          <div class="success-message"><?php echo $review_success; ?></div>
        <?php endif; ?>

        <form method="post" class="review-form">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          
          <div class="form-group">
            <label>Your Rating</label>
            <div class="star-rating-input">
              <?php for ($i = 5; $i >= 1; $i--): ?>
                <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                <label for="star<?php echo $i; ?>">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                  </svg>
                </label>
              <?php endfor; ?>
            </div>
          </div>

          <div class="form-group">
            <label for="comment">Your Review</label>
            <textarea id="comment" name="comment" rows="4" required placeholder="Share your experience with this equipment..."></textarea>
          </div>

          <button type="submit" name="submit_review" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            Submit Review
          </button>
        </form>
      </div>
      <?php endif; ?>

      <!-- Reviews List -->
      <div class="reviews-list">
        <?php if (empty($reviews)): ?>
          <div class="empty-state-sm">
            <div class="empty-icon">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
              </svg>
            </div>
            <h4>No reviews yet</h4>
            <p>Be the first to review this product!</p>
          </div>
        <?php else: ?>
          <?php foreach ($reviews as $review): ?>
          <div class="review-card">
            <div class="review-header">
              <div class="review-author">
                <div class="review-avatar">
                  <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
                </div>
                <div>
                  <div class="review-author-name"><?php echo htmlspecialchars($review['full_name'] ?: $review['username']); ?></div>
                  <div class="review-date"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></div>
                </div>
              </div>
              <div class="review-rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <svg class="star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                  </svg>
                <?php endfor; ?>
              </div>
            </div>
            <div class="review-comment">
              <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
.product-detail-container {
  max-width: 1200px;
  margin: 0 auto;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 2rem;
  font-size: 0.9rem;
  color: #6b7280;
}

.breadcrumb a {
  color: #2e7d32;
  text-decoration: none;
  transition: color 0.2s;
}

.breadcrumb a:hover {
  color: #1b5e20;
  text-decoration: underline;
}

.breadcrumb span:last-child {
  color: #1f2937;
  font-weight: 500;
}

.product-detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  margin-bottom: 3rem;
}

@media (max-width: 968px) {
  .product-detail-grid {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
}

.product-detail-image {
  width: 100%;
  height: 500px;
  border-radius: 16px;
  overflow: hidden;
  background: #f3f4f6;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.product-detail-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-image-placeholder-large {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  gap: 1rem;
}

.product-image-placeholder-large span {
  font-size: 1rem;
  font-weight: 500;
}

.product-info-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

@media (max-width: 640px) {
  .product-info-cards {
    grid-template-columns: 1fr;
  }
}

.info-card {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  border: 1px solid #e5e7eb;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.info-card svg {
  color: #2e7d32;
  flex-shrink: 0;
}

.info-label {
  font-size: 0.8rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.info-value {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1f2937;
}

.product-detail-header {
  margin-bottom: 2rem;
}

.product-detail-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 1rem 0;
  line-height: 1.2;
}

.rating-display {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.stars {
  display: flex;
  gap: 0.25rem;
}

.star {
  color: #d1d5db;
}

.star.filled {
  color: #fbbf24;
}

.rating-text {
  font-size: 0.95rem;
  color: #6b7280;
  font-weight: 500;
}

.product-price-large {
  font-size: 3rem;
  font-weight: 700;
  color: #2e7d32;
  line-height: 1;
}

.price-period {
  font-size: 1.25rem;
  color: #6b7280;
  font-weight: 500;
}

.product-detail-description,
.owner-info,
.booking-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  border: 1px solid #e5e7eb;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.product-detail-description h3,
.owner-info h3,
.booking-section h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 1rem;
}

.product-detail-description p {
  color: #4b5563;
  line-height: 1.7;
  margin: 0;
}

.owner-card {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.owner-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 700;
  flex-shrink: 0;
}

.owner-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
}

.owner-username {
  font-size: 0.9rem;
  color: #6b7280;
}

.owner-notice {
  background: #fef3c7;
  border: 1px solid #fbbf24;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  color: #92400e;
}

.owner-notice svg {
  flex-shrink: 0;
  color: #f59e0b;
}

.owner-notice p {
  margin: 0;
  line-height: 1.6;
}

.owner-notice a {
  color: #2e7d32;
  font-weight: 600;
  text-decoration: none;
}

.owner-notice a:hover {
  text-decoration: underline;
}

.unavailable-notice {
  background: #fee2e2;
  border: 1px solid #ef4444;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  color: #991b1b;
}

.unavailable-notice svg {
  flex-shrink: 0;
  color: #ef4444;
}

.unavailable-notice p {
  margin: 0;
  line-height: 1.6;
}

.price-calculation {
  background: #f0fdf4;
  border: 1px solid #86efac;
  border-radius: 8px;
  padding: 1rem;
  margin: 1rem 0;
}

.calc-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  color: #166534;
}

.calc-row.total {
  border-top: 2px solid #86efac;
  margin-top: 0.5rem;
  padding-top: 0.75rem;
  font-weight: 700;
  font-size: 1.1rem;
  color: #14532d;
}

.calc-note {
  font-size: 0.75rem;
  color: #16a34a;
  margin-top: 0.5rem;
  font-style: italic;
}

/* Reviews Section */
.reviews-section {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  border: 1px solid #e5e7eb;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.reviews-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.reviews-header h2 {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.reviews-summary {
  text-align: center;
}

.reviews-average {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.average-number {
  font-size: 3rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.stars-large {
  display: flex;
  gap: 0.25rem;
}

.reviews-count {
  font-size: 0.9rem;
  color: #6b7280;
}

.write-review-section {
  background: #f9fafb;
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
}

.write-review-section h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 1.5rem;
}

.star-rating-input {
  display: flex;
  flex-direction: row-reverse;
  justify-content: flex-end;
  gap: 0.5rem;
}

.star-rating-input input {
  display: none;
}

.star-rating-input label {
  cursor: pointer;
  transition: transform 0.2s;
}

.star-rating-input label svg {
  color: #d1d5db;
  transition: color 0.2s;
}

.star-rating-input label:hover svg,
.star-rating-input label:hover ~ label svg,
.star-rating-input input:checked ~ label svg {
  color: #fbbf24;
}

.star-rating-input label:hover {
  transform: scale(1.1);
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.review-card {
  background: #f9fafb;
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.review-author {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.review-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  font-weight: 700;
  flex-shrink: 0;
}

.review-author-name {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
}

.review-date {
  font-size: 0.85rem;
  color: #6b7280;
}

.review-rating {
  display: flex;
  gap: 0.25rem;
}

.review-comment {
  color: #4b5563;
  line-height: 1.7;
}

@media (max-width: 768px) {
  .product-detail-title {
    font-size: 2rem;
  }

  .product-price-large {
    font-size: 2.5rem;
  }

  .reviews-header {
    flex-direction: column;
    gap: 1.5rem;
  }

  .product-detail-image {
    height: 350px;
  }
}
</style>

<?php include 'includes/footer.php'; ?>
