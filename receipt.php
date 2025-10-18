<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT b.*, p.title as product_title, p.image_path, p.price as daily_price, u.full_name as renter_name, u.email as renter_email, o.full_name as owner_name, o.email as owner_email FROM bookings b LEFT JOIN products p ON p.id = b.product_id LEFT JOIN users u ON u.id = b.renter_id LEFT JOIN users o ON o.id = b.owner_id WHERE b.id = ?");
$stmt->execute([$id]);
$b = $stmt->fetch();
if (!$b) die('Booking not found.');

// Calculate rental period and total
$start_date = new DateTime($b['start_date']);
$end_date = new DateTime($b['end_date']);
$days = $start_date->diff($end_date)->days + 1; // Include both start and end dates
$total_price = $days * $b['daily_price'];
?>
<?php include 'includes/header.php'; ?>

<div class="container receipt-page">
  <!-- Header Actions -->
  <div class="receipt-actions">
    <a href="dashboard.php" class="btn btn-outline">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      Back to Dashboard
    </a>
    <button class="btn btn-primary" onclick="window.print()">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M6 9V3h12v6M6 21h12a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      Download / Print Receipt
    </button>
  </div>

  <!-- Receipt Card -->
  <div class="receipt-card">
    <!-- Receipt Header -->
    <div class="receipt-header">
      <div class="receipt-brand">
        <div class="brand-logo">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M8 12H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 16V8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div>
          <h1>AgriLease</h1>
          <p class="brand-subtitle">Farm Equipment Rental</p>
        </div>
      </div>
      <div class="receipt-meta">
        <div class="receipt-id">Booking #<?php echo str_pad((int)$b['id'], 6, '0', STR_PAD_LEFT); ?></div>
        <div class="receipt-date"><?php echo date('F j, Y', strtotime($b['created_at'])); ?></div>
        <div class="status-badge status-<?php echo htmlspecialchars($b['status']); ?>">
          <?php echo htmlspecialchars(ucfirst($b['status'])); ?>
        </div>
      </div>
    </div>

    <!-- Receipt Content -->
    <div class="receipt-content">
      <!-- Product Information -->
      <div class="receipt-section">
        <h3 class="section-title">Equipment Details</h3>
        <div class="product-info">
          <div class="product-image">
            <img src="<?php echo htmlspecialchars($b['image_path'] ?: 'assets/images/Harvester2.jpg'); ?>" alt="<?php echo htmlspecialchars($b['product_title']); ?>" />
          </div>
          <div class="product-details">
            <h4><?php echo htmlspecialchars($b['product_title']); ?></h4>
            <div class="product-meta">
              <div class="meta-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Daily Rate: ₹<?php echo number_format((float)$b['daily_price'], 2); ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Rental Period -->
      <div class="receipt-section">
        <h3 class="section-title">Rental Period</h3>
        <div class="rental-period">
          <div class="period-item">
            <div class="period-label">Start Date</div>
            <div class="period-value"><?php echo date('F j, Y', strtotime($b['start_date'])); ?></div>
          </div>
          <div class="period-arrow">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div class="period-item">
            <div class="period-label">End Date</div>
            <div class="period-value"><?php echo date('F j, Y', strtotime($b['end_date'])); ?></div>
          </div>
          <div class="period-days">
            <div class="days-count"><?php echo $days; ?> day<?php echo $days > 1 ? 's' : ''; ?></div>
            <div class="days-label">Total Duration</div>
          </div>
        </div>
      </div>

      <!-- Parties Information -->
      <div class="receipt-section">
        <h3 class="section-title">Parties Involved</h3>
        <div class="parties-grid">
          <div class="party-card">
            <div class="party-header">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <h4>Renter</h4>
            </div>
            <div class="party-details">
              <div class="party-name"><?php echo htmlspecialchars($b['renter_name']); ?></div>
              <div class="party-contact"><?php echo htmlspecialchars($b['renter_email']); ?></div>
            </div>
          </div>

          <div class="party-card">
            <div class="party-header">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <h4>Owner</h4>
            </div>
            <div class="party-details">
              <div class="party-name"><?php echo htmlspecialchars($b['owner_name']); ?></div>
              <div class="party-contact"><?php echo htmlspecialchars($b['owner_email']); ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pricing Breakdown -->
      <div class="receipt-section">
        <h3 class="section-title">Payment Summary</h3>
        <div class="pricing-breakdown">
          <div class="price-row">
            <div class="price-label">Daily Rate × <?php echo $days; ?> days</div>
            <div class="price-amount">₹<?php echo number_format((float)$b['daily_price'], 2); ?></div>
          </div>
          <div class="price-row total">
            <div class="price-label">Total Amount</div>
            <div class="price-amount">₹<?php echo number_format($total_price, 2); ?></div>
          </div>
        </div>
      </div>

      <!-- Map Section -->
      <div class="receipt-section">
        <h3 class="section-title">Location Map</h3>
        <div class="map-container">
          <div id="map"></div>
          <div class="map-legend">
            <div class="legend-item">
              <div class="legend-color owner-marker"></div>
              <span>Equipment Owner</span>
            </div>
            <div class="legend-item">
              <div class="legend-color renter-marker"></div>
              <span>Renter</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Receipt Footer -->
      <div class="receipt-footer">
        <div class="footer-note">
          <p>Thank you for using AgriLease. This receipt serves as confirmation of your equipment rental booking.</p>
          <p>For any questions or concerns, please contact support@agrilease.com</p>
        </div>
        <div class="footer-signature">
          <div class="signature-line"></div>
          <div class="signature-label">AgriLease Representative</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
/* Enhanced Receipt Styles */
:root {
  --primary: #2d7d46;
  --primary-dark: #236136;
  --primary-light: #e8f5e9;
  --secondary: #6c757d;
  --success: #28a745;
  --warning: #ffc107;
  --danger: #dc3545;
  --light: #f8f9fa;
  --dark: #343a40;
  --border: #e9ecef;
  --shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
  --radius: 12px;
  --transition: all 0.3s ease;
}

.receipt-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

/* Header Actions */
.receipt-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  gap: 1rem;
}

/* Receipt Card */
.receipt-card {
  background: white;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
  margin-bottom: 2rem;
}

/* Receipt Header */
.receipt-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 2rem 2rem 1.5rem;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
}

.receipt-brand {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.brand-logo {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
}

.brand-logo svg {
  color: white;
}

.receipt-brand h1 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
}

.brand-subtitle {
  margin: 0.25rem 0 0;
  opacity: 0.9;
  font-size: 0.9rem;
}

.receipt-meta {
  text-align: right;
}

.receipt-id {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.receipt-date {
  font-size: 0.9rem;
  opacity: 0.9;
  margin-bottom: 0.75rem;
}

.status-badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-confirmed {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.status-pending {
  background: rgba(255, 193, 7, 0.2);
  border: 1px solid rgba(255, 193, 7, 0.3);
}

.status-completed {
  background: rgba(40, 167, 69, 0.2);
  border: 1px solid rgba(40, 167, 69, 0.3);
}

/* Receipt Content */
.receipt-content {
  padding: 0 2rem 2rem;
}

.receipt-section {
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--border);
}

.receipt-section:last-of-type {
  border-bottom: none;
  margin-bottom: 0;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Product Information */
.product-info {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: 1.5rem;
  align-items: start;
}

.product-image {
  border-radius: var(--radius);
  overflow: hidden;
  box-shadow: var(--shadow);
}

.product-image img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  display: block;
}

.product-details h4 {
  margin: 0 0 0.75rem;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--dark);
}

.product-meta {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: var(--secondary);
}

.meta-item svg {
  flex-shrink: 0;
}

/* Rental Period */
.rental-period {
  display: grid;
  grid-template-columns: 1fr auto 1fr auto;
  gap: 1rem;
  align-items: center;
}

.period-item {
  text-align: center;
}

.period-label {
  font-size: 0.8rem;
  color: var(--secondary);
  margin-bottom: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.period-value {
  font-size: 1rem;
  font-weight: 600;
  color: var(--dark);
}

.period-arrow {
  color: var(--primary);
}

.period-days {
  text-align: center;
  padding: 0.75rem;
  background: var(--primary-light);
  border-radius: var(--radius);
}

.days-count {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 0.25rem;
}

.days-label {
  font-size: 0.8rem;
  color: var(--secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Parties Grid */
.parties-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.party-card {
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.25rem;
}

.party-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--border);
}

.party-header h4 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--dark);
}

.party-header svg {
  color: var(--primary);
}

.party-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 0.25rem;
}

.party-contact {
  font-size: 0.9rem;
  color: var(--secondary);
}

/* Pricing Breakdown */
.pricing-breakdown {
  max-width: 400px;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
}

.price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border);
}

.price-row:last-child {
  border-bottom: none;
}

.price-row.total {
  background: var(--primary-light);
  font-weight: 700;
}

.price-label {
  color: var(--dark);
}

.price-amount {
  font-weight: 600;
  color: var(--primary);
}

.price-row.total .price-amount {
  font-size: 1.1rem;
}

/* Map Section */
.map-container {
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
}

#map {
  height: 300px;
  width: 100%;
}

.map-legend {
  display: flex;
  gap: 1.5rem;
  padding: 1rem 1.25rem;
  background: var(--light);
  border-top: 1px solid var(--border);
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: var(--secondary);
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.owner-marker {
  background: var(--primary);
}

.renter-marker {
  background: #dc3545;
}

/* Receipt Footer */
.receipt-footer {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border);
}

.footer-note p {
  margin: 0 0 0.5rem;
  font-size: 0.9rem;
  color: var(--secondary);
  line-height: 1.5;
}

.footer-signature {
  text-align: center;
}

.signature-line {
  height: 1px;
  background: var(--border);
  margin-bottom: 0.5rem;
}

.signature-label {
  font-size: 0.8rem;
  color: var(--secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Button Styles */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1.5rem;
  font-size: 0.9rem;
  font-weight: 500;
  text-decoration: none;
  border: none;
  border-radius: var(--radius);
  cursor: pointer;
  transition: var(--transition);
  gap: 0.5rem;
}

.btn-primary {
  background: var(--primary);
  color: white;
}

.btn-primary:hover {
  background: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(45, 125, 70, 0.3);
}

.btn-outline {
  background: transparent;
  color: var(--primary);
  border: 1px solid var(--primary);
}

.btn-outline:hover {
  background: var(--primary-light);
  transform: translateY(-1px);
}

/* Print Styles */
@media print {
  .receipt-actions {
    display: none;
  }
  
  .receipt-card {
    box-shadow: none;
    border: 1px solid var(--border);
  }
  
  .btn {
    display: none;
  }
  
  .receipt-page {
    padding: 0;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .receipt-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .receipt-meta {
    text-align: center;
  }
  
  .receipt-actions {
    flex-direction: column;
  }
  
  .product-info {
    grid-template-columns: 1fr;
    text-align: center;
  }
  
  .rental-period {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }
  
  .period-arrow {
    transform: rotate(90deg);
  }
  
  .parties-grid {
    grid-template-columns: 1fr;
  }
  
  .receipt-footer {
    grid-template-columns: 1fr;
    text-align: center;
  }
  
  .receipt-content {
    padding: 0 1rem 1rem;
  }
  
  .receipt-header {
    padding: 1.5rem 1rem 1rem;
  }
}

@media (max-width: 480px) {
  .receipt-page {
    padding: 1rem 0.5rem;
  }
  
  .map-legend {
    flex-direction: column;
    gap: 0.75rem;
  }
}
</style>

<script>
(function(){
  // Initialize map with enhanced styling
  var rlat = <?php echo $b['renter_lat'] ? $b['renter_lat'] : 'null'; ?>;
  var rlng = <?php echo $b['renter_lng'] ? $b['renter_lng'] : 'null'; ?>;
  var olat = <?php echo $b['owner_lat'] ? $b['owner_lat'] : 'null'; ?>;
  var olng = <?php echo $b['owner_lng'] ? $b['owner_lng'] : 'null'; ?>;
  
  var map = L.map('map').setView([20.6,78.9],5);
  
  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    maxZoom:19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);
  
  // Custom icons
  var ownerIcon = L.divIcon({
    html: '<div style="background-color:#2d7d46; width:12px; height:12px; border-radius:50%; border:2px solid white; box-shadow:0 2px 4px rgba(0,0,0,0.2);"></div>',
    className: 'custom-div-icon',
    iconSize: [16, 16],
    iconAnchor: [8, 8]
  });
  
  var renterIcon = L.divIcon({
    html: '<div style="background-color:#dc3545; width:12px; height:12px; border-radius:50%; border:2px solid white; box-shadow:0 2px 4px rgba(0,0,0,0.2);"></div>',
    className: 'custom-div-icon',
    iconSize: [16, 16],
    iconAnchor: [8, 8]
  });
  
  // Add markers
  if (rlat && rlng) {
    L.marker([rlat, rlng], {icon: renterIcon})
      .addTo(map)
      .bindPopup('<strong>Renter Location</strong><br><?php echo htmlspecialchars($b['renter_name']); ?>');
  }
  
  if (olat && olng) {
    L.marker([olat, olng], {icon: ownerIcon})
      .addTo(map)
      .bindPopup('<strong>Owner Location</strong><br><?php echo htmlspecialchars($b['owner_name']); ?>');
  }
  
  // Set view to show both markers if available
  if (rlat && rlng && olat && olng) {
    var group = new L.featureGroup([
      L.marker([rlat, rlng]),
      L.marker([olat, olng])
    ]);
    map.fitBounds(group.getBounds().pad(0.1));
  } else if (rlat && rlng) {
    map.setView([rlat, rlng], 10);
  } else if (olat && olng) {
    map.setView([olat, olng], 10);
  }
})();
</script>

<?php include 'includes/footer.php'; ?>