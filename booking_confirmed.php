<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$booking_id = (int)($_GET['id'] ?? 0);

if (!$booking_id) {
    header('Location: manage_bookings.php');
    exit;
}

// Fetch complete booking details with receipt
$stmt = $pdo->prepare("
    SELECT 
        b.*,
        p.id as product_id,
        p.title as product_title,
        p.description as product_description,
        p.category,
        p.price as daily_price,
        p.image_path,
        p.location as product_location,
        renter.id as renter_id,
        renter.full_name as renter_name,
        renter.email as renter_email,
        renter.phone as renter_phone,
        renter.address as renter_address,
        owner.id as owner_id,
        owner.full_name as owner_name,
        owner.email as owner_email,
        owner.phone as owner_phone,
        owner.address as owner_address,
        r.receipt_number,
        r.subtotal,
        r.tax_amount,
        r.total_amount as receipt_total,
        r.payment_status as receipt_payment_status
    FROM bookings b
    LEFT JOIN products p ON b.product_id = p.id
    LEFT JOIN users renter ON b.renter_id = renter.id
    LEFT JOIN users owner ON b.owner_id = owner.id
    LEFT JOIN receipts r ON b.id = r.booking_id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: manage_bookings.php');
    exit;
}

// Verify user has access
if ($booking['renter_id'] != $_SESSION['user_id'] && $booking['owner_id'] != $_SESSION['user_id']) {
    die('Access denied.');
}

// Calculate duration
$start_date = new DateTime($booking['start_date']);
$end_date = new DateTime($booking['end_date']);
$duration = $start_date->diff($end_date)->days + 1;

$is_owner = ($booking['owner_id'] == $_SESSION['user_id']);
?>
<?php include 'includes/header.php'; ?>

<div class="container confirmation-page">
    <!-- Success Banner -->
    <div class="success-banner">
        <div class="success-icon-large">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <h1>Booking Confirmed!</h1>
        <p>The rental has been successfully confirmed and a receipt has been generated.</p>
    </div>

    <!-- Booking Details Card -->
    <div class="details-grid">
        <!-- Left Column -->
        <div class="details-column">
            <!-- Booking Summary -->
            <div class="detail-card">
                <div class="card-header">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <h2>Booking Summary</h2>
                </div>
                <div class="card-content">
                    <div class="info-row">
                        <span class="label">Booking ID:</span>
                        <span class="value">#<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <?php if ($booking['receipt_number']): ?>
                    <div class="info-row">
                        <span class="label">Receipt Number:</span>
                        <span class="value highlight"><?php echo htmlspecialchars($booking['receipt_number']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <span class="label">Status:</span>
                        <span class="value">
                            <span class="status-badge status-<?php echo $booking['status']; ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Confirmed On:</span>
                        <span class="value"><?php echo date('F j, Y g:i A', strtotime($booking['updated_at'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Equipment Details -->
            <div class="detail-card">
                <div class="card-header">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    <h2>Equipment Details</h2>
                </div>
                <div class="card-content">
                    <?php if ($booking['image_path']): ?>
                    <div class="equipment-image">
                        <img src="<?php echo htmlspecialchars($booking['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($booking['product_title']); ?>">
                    </div>
                    <?php endif; ?>
                    <h3 class="equipment-title"><?php echo htmlspecialchars($booking['product_title']); ?></h3>
                    <div class="equipment-meta">
                        <span class="badge"><?php echo htmlspecialchars($booking['category']); ?></span>
                        <span class="location">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <?php echo htmlspecialchars($booking['product_location'] ?: 'Not specified'); ?>
                        </span>
                    </div>
                    <?php if ($booking['product_description']): ?>
                    <p class="equipment-description"><?php echo htmlspecialchars($booking['product_description']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rental Period -->
            <div class="detail-card">
                <div class="card-header">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <h2>Rental Period</h2>
                </div>
                <div class="card-content">
                    <div class="date-range">
                        <div class="date-box">
                            <div class="date-label">Start Date</div>
                            <div class="date-value"><?php echo $start_date->format('M j, Y'); ?></div>
                            <div class="date-day"><?php echo $start_date->format('l'); ?></div>
                        </div>
                        <div class="date-arrow">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </div>
                        <div class="date-box">
                            <div class="date-label">End Date</div>
                            <div class="date-value"><?php echo $end_date->format('M j, Y'); ?></div>
                            <div class="date-day"><?php echo $end_date->format('l'); ?></div>
                        </div>
                    </div>
                    <div class="duration-highlight">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <strong><?php echo $duration; ?> day<?php echo $duration > 1 ? 's' : ''; ?></strong> rental period
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="details-column">
            <!-- Payment Details -->
            <div class="detail-card highlight-card">
                <div class="card-header">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                    <h2>Payment Details</h2>
                </div>
                <div class="card-content">
                    <div class="payment-breakdown">
                        <div class="payment-row">
                            <span>Daily Rate</span>
                            <span>₹<?php echo number_format((float)$booking['daily_price'], 2); ?></span>
                        </div>
                        <div class="payment-row">
                            <span>Duration</span>
                            <span><?php echo $duration; ?> day<?php echo $duration > 1 ? 's' : ''; ?></span>
                        </div>
                        <div class="payment-row subtotal">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($booking['subtotal'] ?: ($duration * $booking['daily_price']), 2); ?></span>
                        </div>
                        <div class="payment-row">
                            <span>GST (18%)</span>
                            <span>₹<?php echo number_format($booking['tax_amount'] ?: (($duration * $booking['daily_price']) * 0.18), 2); ?></span>
                        </div>
                        <div class="payment-row total">
                            <span>Total Amount</span>
                            <span>₹<?php echo number_format($booking['receipt_total'] ?: ($booking['total_price'] * 1.18), 2); ?></span>
                        </div>
                    </div>
                    <?php if ($booking['receipt_payment_status']): ?>
                    <div class="payment-status">
                        <span>Payment Status:</span>
                        <span class="status-badge status-<?php echo $booking['receipt_payment_status']; ?>">
                            <?php echo ucfirst($booking['receipt_payment_status']); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="detail-card">
                <div class="card-header">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <h2>Contact Information</h2>
                </div>
                <div class="card-content">
                    <div class="contact-section">
                        <h4><?php echo $is_owner ? 'Renter' : 'Equipment Owner'; ?></h4>
                        <div class="contact-info">
                            <div class="contact-avatar">
                                <?php echo strtoupper(substr($is_owner ? $booking['renter_name'] : $booking['owner_name'], 0, 1)); ?>
                            </div>
                            <div class="contact-details">
                                <div class="contact-name"><?php echo htmlspecialchars($is_owner ? $booking['renter_name'] : $booking['owner_name']); ?></div>
                                <div class="contact-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                    <?php echo htmlspecialchars($is_owner ? $booking['renter_email'] : $booking['owner_email']); ?>
                                </div>
                                <?php if (($is_owner && $booking['renter_phone']) || (!$is_owner && $booking['owner_phone'])): ?>
                                <div class="contact-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                    <?php echo htmlspecialchars($is_owner ? $booking['renter_phone'] : $booking['owner_phone']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <?php if ($booking['notes']): ?>
            <div class="detail-card">
                <div class="card-header">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <h2>Additional Notes</h2>
                </div>
                <div class="card-content">
                    <p class="notes-text"><?php echo nl2br(htmlspecialchars($booking['notes'])); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="receipt.php?id=<?php echo $booking_id; ?>" class="btn btn-primary btn-lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    View Full Receipt
                </a>
                <a href="manage_bookings.php" class="btn btn-outline btn-lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Back to Bookings
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.confirmation-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.success-banner {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 16px;
    padding: 3rem 2rem;
    text-align: center;
    margin-bottom: 3rem;
    box-shadow: 0 10px 40px rgba(16, 185, 129, 0.3);
}

.success-icon-large {
    margin: 0 auto 1.5rem;
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
    from {
        transform: scale(0);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.success-banner h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem;
}

.success-banner p {
    font-size: 1.1rem;
    opacity: 0.95;
    margin: 0;
}

.details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.details-column {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.detail-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.highlight-card {
    border: 2px solid #10b981;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.card-header svg {
    color: #10b981;
    flex-shrink: 0;
}

.card-header h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.card-content {
    padding: 1.5rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row .label {
    color: #6b7280;
    font-weight: 500;
}

.info-row .value {
    color: #1f2937;
    font-weight: 600;
}

.info-row .value.highlight {
    color: #10b981;
    font-size: 1.1rem;
}

.status-badge {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-confirmed {
    background: #d1fae5;
    color: #065f46;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-paid {
    background: #d1fae5;
    color: #065f46;
}

.equipment-image {
    width: 100%;
    height: 200px;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.equipment-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.equipment-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.75rem;
}

.equipment-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.badge {
    background: #10b981;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.location {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    color: #6b7280;
    font-size: 0.9rem;
}

.equipment-description {
    color: #6b7280;
    line-height: 1.6;
    margin: 0;
}

.date-range {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 1.5rem;
    align-items: center;
    margin-bottom: 1.5rem;
}

.date-box {
    text-align: center;
    padding: 1.25rem;
    background: #f9fafb;
    border-radius: 8px;
    border: 2px solid #e5e7eb;
}

.date-label {
    font-size: 0.8rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.date-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.date-day {
    font-size: 0.85rem;
    color: #10b981;
    font-weight: 600;
}

.date-arrow {
    color: #10b981;
}

.duration-highlight {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem;
    background: #d1fae5;
    border-radius: 8px;
    color: #065f46;
    font-size: 1.1rem;
}

.payment-breakdown {
    margin-bottom: 1.5rem;
}

.payment-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
    color: #4b5563;
}

.payment-row.subtotal {
    border-top: 2px solid #e5e7eb;
    margin-top: 0.5rem;
    padding-top: 1rem;
    font-weight: 600;
    color: #1f2937;
}

.payment-row.total {
    border-top: 2px solid #10b981;
    border-bottom: none;
    margin-top: 0.5rem;
    padding-top: 1rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #065f46;
}

.payment-status {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
    font-weight: 600;
}

.contact-section h4 {
    font-size: 0.9rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 1rem;
}

.contact-info {
    display: flex;
    gap: 1rem;
}

.contact-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    flex-shrink: 0;
}

.contact-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 0.375rem;
}

.contact-item svg {
    flex-shrink: 0;
    color: #10b981;
}

.notes-text {
    color: #4b5563;
    line-height: 1.7;
    margin: 0;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-lg {
    padding: 1.25rem 2rem;
    font-size: 1.1rem;
}

.btn-primary {
    background: #10b981;
    color: white;
}

.btn-primary:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
}

.btn-outline {
    background: white;
    color: #6b7280;
    border: 2px solid #e5e7eb;
}

.btn-outline:hover {
    border-color: #10b981;
    color: #10b981;
    transform: translateY(-2px);
}

@media (max-width: 968px) {
    .details-grid {
        grid-template-columns: 1fr;
    }
    
    .success-banner h1 {
        font-size: 2rem;
    }
    
    .date-range {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .date-arrow {
        transform: rotate(90deg);
    }
}

@media print {
    .action-buttons {
        display: none;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
