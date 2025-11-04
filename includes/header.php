<?php // includes/header.php ?>
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
      <a href="dashboard.php">Dashboard</a>
      <a href="products.php">Browse Equipment</a>
      <a href="manage_bookings.php">Manage Bookings</a>
      <a href="my_products.php">My Products</a>
      <a href="add_product.php">Add Product</a>
      <a href="notifications.php">
        Notifications
        <?php 
        if (isset($_SESSION['user_id'])) {
          $unread_count = getUnreadNotificationCount($_SESSION['user_id']);
          if ($unread_count > 0): 
        ?>
          <span class="notification-badge"><?php echo $unread_count; ?></span>
        <?php endif; } ?>
      </a>
      <a href="profile.php">Profile</a>
      <a href="logout.php" class="btn btn-ghost">Logout</a>
      <div class="user-pill">
        <span class="user-avatar">👤</span>
        <span class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
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

// Send form to your email using formsubmit
document.getElementById('booking-form')?.addEventListener('submit', function(e){
  e.preventDefault();
  const fd = new FormData(this);
  fetch('https://formsubmit.co/ajax/bhavikdumore309@gmail.com', {
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
</script>
