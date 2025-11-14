<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>AgriLease</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
<nav class="navbar">
  <div class="container nav-inner">
    <a class="brand" href="dashboard.php"><span class="logo-icon">🌾</span> AgriLease</a>
    <button class="nav-toggle" aria-label="Toggle menu" onclick="document.body.classList.toggle('nav-open')">☰</button>
    <div class="nav-links">
      <a href="dashboard.php" class="nav-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="7" height="7"></rect>
          <rect x="14" y="3" width="7" height="7"></rect>
          <rect x="14" y="14" width="7" height="7"></rect>
          <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
        <span>Dashboard</span>
      </a>
      
      <a href="products.php" class="nav-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <span>Browse Products</span>
      </a>
      
      <a href="my_products.php" class="nav-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 7h-9"></path>
          <path d="M14 17H5"></path>
          <circle cx="17" cy="17" r="3"></circle>
          <circle cx="7" cy="7" r="3"></circle>
        </svg>
        <span>My Listings</span>
      </a>
      
      <a href="manage_bookings.php" class="nav-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <span>Bookings</span>
      </a>
      
      <a href="notifications.php" class="nav-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <span>Notifications</span>
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
        <span>Add Product</span>
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
            Profile Settings
          </a>
          <a href="my_products.php" class="dropdown-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 7h-9"></path>
              <path d="M14 17H5"></path>
              <circle cx="17" cy="17" r="3"></circle>
              <circle cx="7" cy="7" r="3"></circle>
            </svg>
            My Listings
          </a>
          <a href="my_bookings.php" class="dropdown-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14,2 14,8 20,8"></polyline>
            </svg>
            My Bookings
          </a>
          <div class="dropdown-divider"></div>
          <a href="logout.php" class="dropdown-item logout">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Logout
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
    <h2>Booking Request</h2>
    <form id="booking-form">
      <input type="hidden" id="booking-product-id" name="product_id" value="">
      <div class="form-row">
        <label for="bk-name">Your Name</label>
        <input id="bk-name" name="name" type="text" placeholder="Enter your name" required>
      </div>
      <div class="form-row">
        <label for="bk-phone">Phone</label>
        <input id="bk-phone" name="phone" type="tel" placeholder="10-digit phone number" required>
      </div>
      <div class="form-row two">
        <div>
          <label for="bk-date">Date</label>
          <input id="bk-date" name="date" type="date" required>
        </div>
        <div>
          <label for="bk-duration">Duration (days)</label>
          <input id="bk-duration" name="duration" type="number" min="1" max="30" value="1" required>
        </div>
      </div>
      <div class="form-row">
        <label for="bk-message">Message (optional)</label>
        <textarea id="bk-message" name="message" rows="3" placeholder="Anything we should know?"></textarea>
      </div>
      <button type="submit" class="btn">Send Request</button>
    </form>
  </div>
</div>

<script>

function openModal(productId, productTitle){
  document.getElementById('booking-product-id').value = productId;
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


document.getElementById('booking-form')?.addEventListener('submit', function(e){
  e.preventDefault();
  const fd = new FormData(this);
  fetch('https:
    method: 'POST',
    body: fd
  })
  .then(r => r.json())
  .then(data => {
    alert('Booking request sent successfully.');
    this.reset();
    closeModal();
  })
  .catch(() => alert('Could not send booking request. Please try again.'));
});


document.addEventListener('click', function(e) {
  const userMenu = document.querySelector('.user-menu');
  const dropdown = document.querySelector('.user-dropdown');
  
  if (userMenu && dropdown && !userMenu.contains(e.target)) {
    dropdown.classList.remove('show');
  }
});
</script>
