<?php ?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo t('app_name'); ?></title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
<nav class="navbar">
  <div class="container nav-inner">
    <a class="brand" href="dashboard.php"><span class="logo-icon">🌾</span> <?php echo t('app_name'); ?></a>
    <button class="nav-toggle" aria-label="<?php echo t('toggle_menu'); ?>" onclick="document.body.classList.toggle('nav-open')">☰</button>
    <div class="nav-links">
      <a href="dashboard.php" class="nav-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="7" height="7"></rect>
          <rect x="14" y="3" width="7" height="7"></rect>
          <rect x="14" y="14" width="7" height="7"></rect>
          <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
        <span><?php echo t('nav_dashboard'); ?></span>
      </a>
      
      <a href="products.php" class="nav-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <span><?php echo t('nav_browse'); ?></span>
      </a>
      
      <a href="my_products.php" class="nav-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 7h-9"></path>
          <path d="M14 17H5"></path>
          <circle cx="17" cy="17" r="3"></circle>
          <circle cx="7" cy="7" r="3"></circle>
        </svg>
        <span><?php echo t('nav_my_listings'); ?></span>
      </a>
      
      <a href="manage_bookings.php" class="nav-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <span><?php echo t('nav_bookings'); ?></span>
      </a>
      
      <a href="notifications.php" class="nav-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <span><?php echo t('nav_notifications'); ?></span>
        <?php 
        if (isset($_SESSION['user_id'])) {
          $unread_count = getUnreadNotificationCount($_SESSION['user_id']);
          if ($unread_count > 0): 
        ?>
          <span class="notification-badge"><?php echo $unread_count; ?></span>
        <?php endif; } ?>
      </a>
      
      <div class="nav-divider"></div>
      
      <a href="add_product.php" class="nav-link nav-link-primary">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span><?php echo t('nav_add_product'); ?></span>
      </a>

      <!-- Language Switcher -->
      <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        $target_lang  = ($_SESSION['lang'] ?? 'en') === 'en' ? 'mr' : 'en';
        $lang_params  = $_GET;
        $lang_params['lang'] = $target_lang;
        $lang_query   = http_build_query($lang_params);
      ?>
      <a href="<?php echo htmlspecialchars($current_page . '?' . $lang_query); ?>" class="nav-link lang-switcher" title="Switch language">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="2" y1="12" x2="22" y2="12"></line>
          <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
        </svg>
        <span><?php echo t('switch_lang'); ?></span>
      </a>
      
      <div class="user-menu">
        <button class="user-pill" onclick="document.querySelector('.user-dropdown').classList.toggle('show')">
          <span class="user-avatar">👤</span>
          <span class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <div class="user-dropdown">
          <a href="profile.php" class="dropdown-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <?php echo t('nav_profile'); ?>
          </a>
          <a href="my_products.php" class="dropdown-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 7h-9"></path>
              <path d="M14 17H5"></path>
              <circle cx="17" cy="17" r="3"></circle>
              <circle cx="7" cy="7" r="3"></circle>
            </svg>
            <?php echo t('nav_my_listings'); ?>
          </a>
          <a href="my_bookings.php" class="dropdown-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14,2 14,8 20,8"></polyline>
            </svg>
            <?php echo t('nav_my_bookings'); ?>
          </a>
          <div class="dropdown-divider"></div>
          <a href="logout.php" class="dropdown-item logout">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <?php echo t('nav_logout'); ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>

<!-- Booking Modal -->
<div id="booking-modal" class="modal" style="display:none;">
  <div class="modal-content">
    <button class="close-modal" onclick="closeModal()" aria-label="Close">×</button>
    <h2><?php echo t('bk_modal_title'); ?></h2>
    <form id="booking-form" method="post" action="book.php">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <input type="hidden" id="booking-product-id" name="product_id" value="">
      <div class="form-row two">
        <div>
          <label for="bk-start-date"><?php echo t('bk_date'); ?></label>
          <input id="bk-start-date" name="start_date" type="date" required min="<?php echo date('Y-m-d'); ?>">
        </div>
        <div>
          <label for="bk-end-date">End Date</label>
          <input id="bk-end-date" name="end_date" type="date" required min="<?php echo date('Y-m-d'); ?>">
        </div>
      </div>
      <div class="form-row">
        <label for="bk-message"><?php echo t('bk_message'); ?></label>
        <textarea id="bk-message" name="notes" rows="3" placeholder="<?php echo t('bk_message_ph'); ?>"></textarea>
      </div>
      <button type="submit" class="btn"><?php echo t('bk_send'); ?></button>
    </form>
  </div>
</div>

<script>
var _bkSentOk   = <?php echo json_encode(t('bk_sent_ok')); ?>;
var _bkSentFail = <?php echo json_encode(t('bk_sent_fail')); ?>;

function openModal(productId, productTitle){
  document.getElementById('booking-product-id').value = productId;
  // Reset date min values to today
  var today = new Date().toISOString().split('T')[0];
  document.getElementById('bk-start-date').min = today;
  document.getElementById('bk-end-date').min = today;
  document.getElementById('booking-modal').style.display = 'flex';
}
function closeModal(){
  document.getElementById('booking-modal').style.display = 'none';
}
document.addEventListener('click', (e)=>{
  if(e.target.classList.contains('book-btn')){
    const id = e.target.dataset.id;
    const title = e.target.dataset.title || '';
    openModal(id, title);
  }
});

// Enforce end_date >= start_date
document.getElementById('bk-start-date')?.addEventListener('change', function() {
  var endInput = document.getElementById('bk-end-date');
  if (endInput.value && endInput.value < this.value) {
    endInput.value = this.value;
  }
  endInput.min = this.value;
});

document.addEventListener('click', function(e) {
  const userMenu = document.querySelector('.user-menu');
  const dropdown = document.querySelector('.user-dropdown');
  
  if (userMenu && dropdown && !userMenu.contains(e.target)) {
    dropdown.classList.remove('show');
  }
});
</script>
