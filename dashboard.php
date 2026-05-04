<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $product_id = (int)$_POST['product_id'];
        

        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND listed_by = ?");
        $stmt->execute([$product_id, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {

            $delete_stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND listed_by = ?");
            $delete_stmt->execute([$product_id, $_SESSION['user_id']]);
            

            header('Location: dashboard.php?deleted=1');
            exit;
        }
    }
}


$stmt = $pdo->query("SELECT p.*, u.username, u.full_name FROM products p LEFT JOIN users u ON u.id = p.listed_by ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();


$stmt = $pdo->prepare("SELECT b.*, p.title as product_title FROM bookings b LEFT JOIN products p ON p.id = b.product_id WHERE b.renter_id = ? OR b.owner_id = ? ORDER BY b.created_at DESC");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$bookings = $stmt->fetchAll();


$myProducts = [];
$otherProducts = [];
foreach ($products as $p) {
    if ((int)$p['listed_by'] === (int)($_SESSION['user_id'] ?? 0)) {
        $myProducts[] = $p;
    } else {
        $otherProducts[] = $p;
    }
}


$receivedBookings = [];
foreach ($bookings as $b) {
    if ((int)$b['renter_id'] !== (int)($_SESSION['user_id'] ?? 0)) {
        $receivedBookings[] = $b;
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="dashboard-container">
  <!-- Dashboard Header -->
  <div class="dashboard-hero">
    <div class="hero-content">
      <div class="welcome-section">
        <h1 class="hero-title"><?php echo t('dash_welcome'); ?> <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</h1>
        <p class="hero-subtitle"><?php echo t('dash_subtitle'); ?></p>
      </div>
      <div class="hero-actions">
        <a href="add_product.php" class="btn btn-primary btn-icon">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M8 1V15M1 8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <?php echo t('dash_add_product'); ?>
        </a>
      </div>
    </div>
  </div>

  <!-- Stats Overview -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon-wrapper">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 12H15M9 16H15M9 20H15M5 4H19C20.1046 4 21 4.89543 21 6V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
      </div>
      <div class="stat-content">
        <div class="stat-value"><?php echo count($myProducts); ?></div>
        <div class="stat-label"><?php echo t('dash_my_listings'); ?></div>
        <div class="stat-trend">
          <svg width="12" height="12" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14 10L8.5 4.5L5.5 7.5L2 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 4H14V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span><?php echo t('dash_active'); ?></span>
        </div>
      </div>
    </div>
    
    <div class="stat-card">
      <div class="stat-icon-wrapper">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M8 7V3M16 7V3M7 11H17M5 21H19C20.1046 21 21 20.1046 21 19V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V19C3 20.1046 3.89543 21 5 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
      </div>
      <div class="stat-content">
        <div class="stat-value"><?php echo count($receivedBookings); ?></div>
        <div class="stat-label"><?php echo t('dash_received_bookings'); ?></div>
        <div class="stat-trend">
          <svg width="12" height="12" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14 10L8.5 4.5L5.5 7.5L2 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 4H14V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span><?php echo t('dash_pending'); ?>: <?php echo count(array_filter($receivedBookings, function($b) { return $b['status'] === 'pending'; })); ?></span>
        </div>
      </div>
    </div>
    
    <div class="stat-card">
      <div class="stat-icon-wrapper">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 10H21M7 15H8M12 15H13M6 19H18C19.6569 19 21 17.6569 21 16V8C21 6.34315 19.6569 5 18 5H6C4.34315 5 3 6.34315 3 8V16C3 17.6569 4.34315 19 6 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
      </div>
      <div class="stat-content">
        <div class="stat-value"><?php echo count($otherProducts); ?></div>
        <div class="stat-label"><?php echo t('dash_available_products'); ?></div>
        <div class="stat-trend">
          <svg width="12" height="12" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14 10L8.5 4.5L5.5 7.5L2 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 4H14V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span><?php echo t('dash_browse_all'); ?></span>
        </div>
      </div>
    </div>
    
    <div class="stat-card">
      <div class="stat-icon-wrapper">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 1V3M12 21V23M4.22 4.22L5.64 5.64M18.36 18.36L19.78 19.78M1 12H3M21 12H23M4.22 19.78L5.64 18.36M18.36 5.64L19.78 4.22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 17C14.7614 17 17 14.7614 17 12C17 9.23858 14.7614 7 12 7C9.23858 7 7 9.23858 7 12C7 14.7614 9.23858 17 12 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
      </div>
      <div class="stat-content">
        <div class="stat-value">₹<?php 
          $totalEarnings = 0;
          foreach ($receivedBookings as $b) {
            if ($b['status'] === 'confirmed') {
              $totalEarnings += (float)$b['total_price'];
            }
          }
          echo number_format($totalEarnings, 2);
        ?></div>
        <div class="stat-label"><?php echo t('dash_total_earnings'); ?></div>
        <div class="stat-trend">
          <svg width="12" height="12" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M8 12.6667L11.3333 9.33333M8 12.6667L4.66667 9.33333M8 12.6667V3.33333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span><?php echo t('dash_this_month'); ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content Grid -->
  <div class="dashboard-content">
    <!-- Left Column: Products -->
    <div class="content-main">
      <!-- My Products Section -->
      <section class="dashboard-section">
        <div class="section-header">
          <div class="section-title">
            <h2><?php echo t('dash_my_products'); ?></h2>
            <span class="badge badge-count"><?php echo count($myProducts); ?></span>
          </div>
          <div class="section-actions">
            <a href="add_product.php" class="btn btn-outline btn-sm btn-icon">
              <svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 1V15M1 8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <?php echo t('dash_add_new'); ?>
            </a>
          </div>
        </div>
        
        <?php if ($myProducts): ?>
        <div class="products-grid">
          <?php foreach ($myProducts as $p): ?>
          <div class="card product-card">
            <div class="product-image-container">
              <?php if ($p['image_path']): ?>
                <img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="Product image" class="product-image">
              <?php else: ?>
                <div class="product-image-placeholder">
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 16L8.5 10.5L11 13.5L14.5 9L16 11M20 16V18C20 19.1046 19.1046 20 18 20H6C4.89543 20 4 19.1046 4 18V6C4 4.89543 4.89543 4 6 4H18C19.1046 4 20 4.89543 20 6V16Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span>No Image</span>
                </div>
              <?php endif; ?>
              <div class="product-status">
                <?php if (isset($p['availability']) && $p['availability'] === 'Available'): ?>
                  <span class="badge badge-success"><?php echo t('status_available'); ?></span>
                <?php elseif (isset($p['availability']) && $p['availability'] === 'Rented'): ?>
                  <span class="badge badge-warning"><?php echo t('status_rented'); ?></span>
                <?php endif; ?>
              </div>
              <div class="product-overlay">
                <div class="overlay-actions">
                  <a href="product_detail.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-light btn-sm">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M8 14C11.3137 14 14 11.3137 14 8C14 4.68629 11.3137 2 8 2C4.68629 2 2 4.68629 2 8C2 11.3137 4.68629 14 8 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                      <path d="M8 10C9.10457 10 10 9.10457 10 8C10 6.89543 9.10457 6 8 6C6.89543 6 6 6.89543 6 8C6 9.10457 6.89543 10 8 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                      <path d="M8.99998 8C8.99998 8 9.74998 6.875 10.825 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php echo t('dash_view'); ?>
                  </a>
                  <a href="edit_product.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-light btn-sm">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M11.3333 1.99996C11.5084 1.82485 11.7163 1.686 11.945 1.59124C12.1737 1.49648 12.4189 1.44763 12.6667 1.44763C12.9144 1.44763 13.1596 1.49648 13.3883 1.59124C13.617 1.686 13.8249 1.82485 14 1.99996C14.1751 2.17507 14.314 2.38297 14.4087 2.61167C14.5035 2.84037 14.5523 3.08555 14.5523 3.33329C14.5523 3.58104 14.5035 3.82622 14.4087 4.05492C14.314 4.28362 14.1751 4.49152 14 4.66663L4.99996 13.6666L1.33329 14.6666L2.33329 11L11.3333 1.99996Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php echo t('dash_edit'); ?>
                  </a>
                </div>
              </div>
            </div>
            
            <div class="card-body">
              <div class="product-category">
                <span class="category-badge"><?php echo htmlspecialchars($p['category'] ?? 'General'); ?></span>
              </div>
              
              <h3 class="product-title"><?php echo htmlspecialchars($p['title']); ?></h3>
              
              <?php if ($p['description']): ?>
                <p class="product-description">
                  <?php echo htmlspecialchars(mb_strimwidth($p['description'], 0, 110, '…')); ?>
                </p>
              <?php endif; ?>
              
              <div class="product-footer">
                <div class="price">₹<?php echo number_format((float)$p['price'], 2); ?><?php echo t('dash_per_day'); ?></div>
                <div class="meta">
                  <small><?php echo t('dash_listed'); ?> <?php echo date('M j, Y', strtotime($p['created_at'])); ?></small>
                </div>
              </div>
              
              <div class="product-actions">
                <form method="post" class="delete-form" onsubmit="return confirm('<?php echo t('dash_delete_confirm'); ?>');">
                  <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                  <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                  <button class="btn btn-danger btn-sm btn-icon" type="submit" name="delete" value="1">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M2 4H3.33333H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                      <path d="M5.33325 4V2.66667C5.33325 2.31305 5.47373 1.97391 5.72378 1.72386C5.97383 1.47381 6.31296 1.33333 6.66659 1.33333H9.33325C9.68687 1.33333 10.026 1.47381 10.2761 1.72386C10.5261 1.97391 10.6666 2.31305 10.6666 2.66667V4M12.6666 4V13.3333C12.6666 13.687 12.5261 14.0261 12.2761 14.2761C12.026 14.5262 11.6869 14.6667 11.3333 14.6667H4.66659C4.31296 14.6667 3.97382 14.5262 3.72378 14.2761C3.47373 14.0261 3.33325 13.687 3.33325 13.3333V4H12.6666Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php echo t('dash_delete'); ?>
                  </button>
                </form>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
          <div class="empty-state">
            <div class="empty-icon">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 12H15M9 16H15M5 20H19C20.1046 20 21 19.1046 21 18V6C21 4.89543 20.1046 4 19 4H5C3.89543 4 3 4.89543 3 6V18C3 19.1046 3.89543 20 5 20Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </div>
            <h3><?php echo t('dash_no_products'); ?></h3>
            <p><?php echo t('dash_no_products_sub'); ?></p>
            <a href="add_product.php" class="btn btn-primary"><?php echo t('dash_add_first'); ?></a>
          </div>
        <?php endif; ?>
      </section>

      <!-- Browse Products Section -->
      <section class="dashboard-section">
        <div class="section-header">
          <div class="section-title">
            <h2><?php echo t('dash_browse_products'); ?></h2>
            <span class="badge badge-count"><?php echo count($otherProducts); ?></span>
          </div>
          <div class="section-actions">
            <a href="products.php" class="btn btn-outline btn-sm"><?php echo t('dash_view_all'); ?></a>
          </div>
        </div>
        
        <?php if ($otherProducts): ?>
        <div class="products-grid">
          <?php foreach ($otherProducts as $p): ?>
          <div class="card product-card">
            <div class="product-image-container">
              <?php if ($p['image_path']): ?>
                <img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="Product image" class="product-image">
              <?php else: ?>
                <div class="product-image-placeholder">
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 16L8.5 10.5L11 13.5L14.5 9L16 11M20 16V18C20 19.1046 19.1046 20 18 20H6C4.89543 20 4 19.1046 4 18V6C4 4.89543 4.89543 4 6 4H18C19.1046 4 20 4.89543 20 6V16Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span>No Image</span>
                </div>
              <?php endif; ?>
              <div class="product-overlay">
                <div class="overlay-actions">
                  <a href="product_detail.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-light btn-sm"><?php echo t('dash_view_details'); ?></a>
                  <a href="product_detail.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-primary btn-sm btn-icon">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M2.66675 4H13.3334V13.3333C13.3334 13.687 13.1929 14.0261 12.9429 14.2761C12.6928 14.5262 12.3537 14.6667 12.0001 14.6667H4.00008C3.64646 14.6667 3.30732 14.5262 3.05727 14.2761C2.80722 14.0261 2.66675 13.687 2.66675 13.3333V4Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                      <path d="M10.6667 2.66667H5.33341C4.97979 2.66667 4.64065 2.80714 4.3906 3.05719C4.14055 3.30724 4.00008 3.64638 4.00008 4V4H12.0001V4C12.0001 3.64638 11.8596 3.30724 11.6096 3.05719C11.3595 2.80714 11.0204 2.66667 10.6667 2.66667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php echo t('dash_book_now'); ?>
                  </a>
                </div>
              </div>
            </div>
            
            <div class="card-body">
              <div class="product-category">
                <span class="category-badge"><?php echo htmlspecialchars($p['category'] ?? 'General'); ?></span>
              </div>
              
              <h3 class="product-title"><?php echo htmlspecialchars($p['title']); ?></h3>
              
              <?php if ($p['description']): ?>
                <p class="product-description">
                  <?php echo htmlspecialchars(mb_strimwidth($p['description'], 0, 110, '…')); ?>
                </p>
              <?php endif; ?>
              
              <div class="product-footer">
                <div class="price">₹<?php echo number_format((float)$p['price'], 2); ?><?php echo t('dash_per_day'); ?></div>
                <div class="meta">
                  <span class="owner">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M13.3334 14V12.6667C13.3334 11.9594 13.0525 11.2811 12.5524 10.781C12.0523 10.281 11.374 10 10.6667 10H5.33341C4.62617 10 3.94789 10.281 3.4478 10.781C2.9477 11.2811 2.66675 11.9594 2.66675 12.6667V14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                      <path d="M8.00008 7.33333C9.47285 7.33333 10.6667 6.13943 10.6667 4.66667C10.6667 3.19391 9.47285 2 8.00008 2C6.52732 2 5.33341 3.19391 5.33341 4.66667C5.33341 6.13943 6.52732 7.33333 8.00008 7.33333Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php echo htmlspecialchars($p['username'] ?? $p['full_name'] ?? 'Unknown'); ?>
                  </span>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
          <div class="empty-state">
            <div class="empty-icon">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 10H21M7 15H8M12 15H13M6 19H18C19.6569 19 21 17.6569 21 16V8C21 6.34315 19.6569 5 18 5H6C4.34315 5 3 6.34315 3 8V16C3 17.6569 4.34315 19 6 19Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </div>
            <h3><?php echo t('dash_no_avail'); ?></h3>
            <p><?php echo t('dash_no_avail_sub'); ?></p>
          </div>
        <?php endif; ?>
      </section>
    </div>

    <!-- Right Column: Bookings -->
    <div class="content-sidebar">
      <!-- Received Bookings Section -->
      <section class="dashboard-section">
        <div class="section-header">
          <div class="section-title">
            <h2><?php echo t('dash_received_bk'); ?></h2>
            <span class="badge badge-count"><?php echo count($receivedBookings); ?></span>
          </div>
        </div>
        
        <?php if ($receivedBookings): ?>
        <div class="bookings-list">
          <?php foreach ($receivedBookings as $b): ?>
          <div class="card booking-card">
            <div class="booking-header">
              <div class="booking-id"><?php echo t('dash_booking_id'); ?><?php echo (int)$b['id']; ?></div>
              <span class="badge badge-<?php 
                echo $b['status'] === 'confirmed' ? 'success' : 
                     ($b['status'] === 'pending' ? 'warning' : 'secondary'); 
              ?>">
                <?php echo htmlspecialchars(ucfirst($b['status'])); ?>
              </span>
            </div>
            
            <h4 class="booking-title"><?php echo htmlspecialchars($b['product_title']); ?></h4>
            
            <?php if (isset($b['start_date']) && isset($b['end_date'])): ?>
              <div class="booking-dates">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12.6667 2.66667H3.33333C2.59695 2.66667 2 3.26362 2 4V13.3333C2 14.0697 2.59695 14.6667 3.33333 14.6667H12.6667C13.403 14.6667 14 14.0697 14 13.3333V4C14 3.26362 13.403 2.66667 12.6667 2.66667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M2 6H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M5.33325 1.33333V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M10.6667 1.33333V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <?php echo date('M j', strtotime($b['start_date'])); ?> - 
                <?php echo date('M j, Y', strtotime($b['end_date'])); ?>
              </div>
            <?php endif; ?>
            
            <?php if (isset($b['total_price'])): ?>
              <div class="booking-price">₹<?php echo number_format((float)$b['total_price'], 2); ?></div>
            <?php endif; ?>
            
            <div class="booking-meta">
              <svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 14C11.3137 14 14 11.3137 14 8C14 4.68629 11.3137 2 8 2C4.68629 2 2 4.68629 2 8C2 11.3137 4.68629 14 8 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8 4V8L10.6667 9.33333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <?php echo t('dash_received'); ?> <?php echo date('M j, Y', strtotime($b['created_at'])); ?>
            </div>
            
            <div class="booking-actions">
              <a href="receipt.php?id=<?php echo (int)$b['id']; ?>" class="btn btn-outline btn-sm"><?php echo t('dash_view_details'); ?></a>
              <?php if ($b['status'] === 'pending'): ?>
                <form method="post" action="update_booking.php" class="booking-action-form">
                  <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                  <input type="hidden" name="booking_id" value="<?php echo (int)$b['id']; ?>">
                  <input type="hidden" name="status" value="confirmed">
                  <button class="btn btn-success btn-sm" type="submit"><?php echo t('dash_confirm'); ?></button>
                </form>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
          <div class="empty-state empty-state-sm">
            <div class="empty-icon">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 7V3M16 7V3M7 11H17M5 21H19C20.1046 21 21 20.1046 21 19V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V19C3 20.1046 3.89543 21 5 21Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </div>
            <h4><?php echo t('dash_no_bookings'); ?></h4>
            <p><?php echo t('dash_no_bookings_sub'); ?></p>
          </div>
        <?php endif; ?>
      </section>
      
      <!-- Quick Actions -->
      <section class="dashboard-section">
        <div class="section-header">
          <h2><?php echo t('dash_quick_actions'); ?></h2>
        </div>
        <div class="quick-actions">
          <a href="products.php" class="quick-action">
            <div class="quick-action-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 10H21M7 15H8M12 15H13M6 19H18C19.6569 19 21 17.6569 21 16V8C21 6.34315 19.6569 5 18 5H6C4.34315 5 3 6.34315 3 8V16C3 17.6569 4.34315 19 6 19Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </div>
            <div class="quick-action-content">
              <h4><?php echo t('dash_browse_all_prod'); ?></h4>
              <p><?php echo t('dash_explore'); ?></p>
            </div>
          </a>
          
          <a href="my_bookings.php" class="quick-action">
            <div class="quick-action-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 7V3M16 7V3M7 11H17M5 21H19C20.1046 21 21 20.1046 21 19V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V19C3 20.1046 3.89543 21 5 21Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </div>
            <div class="quick-action-content">
              <h4><?php echo t('dash_my_bk'); ?></h4>
              <p><?php echo t('dash_view_bk'); ?></p>
            </div>
          </a>
          
          <a href="profile.php" class="quick-action">
            <div class="quick-action-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M6 20C6 17.7909 7.79086 16 10 16H14C16.2091 16 18 17.7909 18 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="quick-action-content">
              <h4><?php echo t('dash_profile_settings'); ?></h4>
              <p><?php echo t('dash_update_account'); ?></p>
            </div>
          </a>
        </div>
      </section>
    </div>
  </div>
</div>

<style>
/* Enhanced Dashboard Styles */
:root {
  --primary: #4361ee;
  --primary-light: #eef2ff;
  --primary-dark: #3a56d4;
  --secondary: #6c757d;
  --success: #28a745;
  --warning: #ffc107;
  --danger: #dc3545;
  --light: #f8f9fa;
  --dark: #343a40;
  --border: #e9ecef;
  --shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --radius: 12px;
  --radius-sm: 8px;
}

.dashboard-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 1rem;
}

/* Hero Section */
.dashboard-hero {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  border-radius: var(--radius);
  padding: 2.5rem;
  margin-bottom: 2rem;
  color: white;
  box-shadow: var(--shadow-lg);
}

.hero-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.hero-title {
  margin: 0 0 0.5rem;
  font-size: 2rem;
  font-weight: 700;
}

.hero-subtitle {
  margin: 0;
  font-size: 1.1rem;
  opacity: 0.9;
}

.hero-actions .btn {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  backdrop-filter: blur(10px);
}

.hero-actions .btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2.5rem;
}

.stat-card {
  background: white;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 1.5rem;
  display: flex;
  align-items: center;
  transition: transform 0.2s, box-shadow 0.2s;
  border-left: 4px solid var(--primary);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.stat-icon-wrapper {
  margin-right: 1rem;
  flex-shrink: 0;
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--primary-light);
  color: var(--primary);
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--dark);
  line-height: 1;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.9rem;
  color: var(--secondary);
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  color: var(--success);
  font-weight: 500;
}

/* Dashboard Content */
.dashboard-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.content-main, .content-sidebar {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.dashboard-section {
  background: white;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 1.5rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border);
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.section-title h2 {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 600;
  color: var(--dark);
}

.section-actions {
  display: flex;
  gap: 0.5rem;
}

/* Products Grid */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.product-card {
  display: flex;
  flex-direction: column;
  height: 100%;
  border-radius: var(--radius-sm);
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.product-image-container {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.product-card:hover .product-image {
  transform: scale(1.05);
}

.product-image-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  background: var(--light);
  color: var(--secondary);
  padding: 1rem;
}

.product-status {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
}

.product-overlay {
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

.product-card:hover .product-overlay {
  opacity: 1;
}

.overlay-actions {
  display: flex;
  gap: 0.5rem;
}

.card-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.product-category {
  margin-bottom: 0.75rem;
}

.category-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  background: var(--light);
  color: var(--secondary);
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.product-title {
  margin: 0 0 0.75rem;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--dark);
  line-height: 1.3;
}

.product-description {
  color: var(--secondary);
  font-size: 0.9rem;
  line-height: 1.5;
  margin-bottom: 1rem;
  flex-grow: 1;
}

.product-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.price {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--primary);
}

.meta {
  font-size: 0.8rem;
  color: var(--secondary);
}

.owner {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.product-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: auto;
}

.product-actions .btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.85rem;
}

.delete-form, .book-form {
  flex: 1;
  display: flex;
}

/* Booking Cards */
.bookings-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.booking-card {
  padding: 1.25rem;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  transition: transform 0.2s, box-shadow 0.2s;
}

.booking-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow);
}

.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.booking-id {
  font-weight: 600;
  color: var(--dark);
  font-size: 0.9rem;
}

.booking-title {
  margin: 0 0 0.75rem;
  font-size: 1rem;
  font-weight: 600;
  color: var(--dark);
  line-height: 1.3;
}

.booking-dates {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: var(--secondary);
  margin-bottom: 0.5rem;
}

.booking-price {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 0.5rem;
}

.booking-meta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  color: var(--secondary);
  margin-bottom: 1rem;
}

.booking-actions {
  display: flex;
  gap: 0.5rem;
}

.booking-actions .btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.85rem;
}

.booking-action-form {
  flex: 1;
  display: flex;
}

/* Quick Actions */
.quick-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.quick-action {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  transition: all 0.2s;
  text-decoration: none;
  color: inherit;
}

.quick-action:hover {
  background: var(--primary-light);
  border-color: var(--primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow);
}

.quick-action-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--primary-light);
  color: var(--primary);
  flex-shrink: 0;
}

.quick-action-content h4 {
  margin: 0 0 0.25rem;
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--dark);
}

.quick-action-content p {
  margin: 0;
  font-size: 0.8rem;
  color: var(--secondary);
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1.5rem;
  font-size: 0.9rem;
  font-weight: 500;
  text-decoration: none;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all 0.2s;
  gap: 0.5rem;
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.85rem;
}

.btn-primary {
  background: var(--primary);
  color: white;
}

.btn-primary:hover {
  background: var(--primary-dark);
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
}

.btn-outline {
  background: transparent;
  color: var(--primary);
  border: 1px solid var(--primary);
}

.btn-outline:hover {
  background: var(--primary-light);
  transform: translateY(-2px);
}

.btn-danger {
  background: var(--danger);
  color: white;
}

.btn-danger:hover {
  background: #c82333;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}

.btn-success {
  background: var(--success);
  color: white;
}

.btn-success:hover {
  background: #218838;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

.btn-light {
  background: rgba(255, 255, 255, 0.9);
  color: var(--dark);
}

.btn-light:hover {
  background: white;
  transform: translateY(-2px);
}

.btn-icon {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

/* Badges */
.badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-radius: 4px;
}

.badge-count {
  background: var(--primary);
  color: white;
}

.badge-success {
  background: #d4edda;
  color: #155724;
}

.badge-warning {
  background: #fff3cd;
  color: #856404;
}

.badge-secondary {
  background: var(--light);
  color: var(--secondary);
}

/* Empty States */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 3rem 2rem;
  background: white;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
}

.empty-state-sm {
  padding: 2rem 1.5rem;
}

.empty-icon {
  margin-bottom: 1rem;
  color: var(--secondary);
  opacity: 0.5;
}

.empty-state h3, .empty-state h4 {
  margin: 0 0 0.5rem;
  font-size: 1.2rem;
  color: var(--dark);
}

.empty-state p {
  margin: 0 0 1.5rem;
  color: var(--secondary);
  max-width: 300px;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .dashboard-content {
    grid-template-columns: 1fr;
  }
  
  .content-sidebar {
    order: -1;
  }
}

@media (max-width: 992px) {
  .hero-content {
    flex-direction: column;
    gap: 1.5rem;
    text-align: center;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .dashboard-hero {
    padding: 2rem 1.5rem;
  }
  
  .hero-title {
    font-size: 1.6rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .products-grid {
    grid-template-columns: 1fr;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .section-actions {
    width: 100%;
    justify-content: flex-end;
  }
  
  .product-actions, .booking-actions {
    flex-direction: column;
  }
  
  .product-footer {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
}

@media (max-width: 576px) {
  .dashboard-container {
    padding: 0 0.5rem;
  }
  
  .dashboard-hero {
    padding: 1.5rem 1rem;
    border-radius: var(--radius-sm);
  }
  
  .hero-title {
    font-size: 1.4rem;
  }
  
  .dashboard-section {
    padding: 1.25rem;
    border-radius: var(--radius-sm);
  }
  
  .stat-card {
    padding: 1.25rem;
  }
}
</style>

<?php include 'includes/footer.php'; ?>
