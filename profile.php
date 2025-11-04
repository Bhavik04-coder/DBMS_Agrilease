<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $full_name = sanitizeInput($_POST['full_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $lat = is_numeric($_POST['lat'] ?? null) ? (float)$_POST['lat'] : null;
        $lng = is_numeric($_POST['lng'] ?? null) ? (float)$_POST['lng'] : null;

        // Validate inputs
        if ($email && !validateEmail($email)) {
            $error = 'Invalid email address.';
        } elseif ($phone && !validatePhone($phone)) {
            $error = 'Phone number must be 10-15 digits.';
        } else {
            // Handle profile image upload
            $profile_image = $user['profile_image'];
            if (!empty($_FILES['profile_image']['name'])) {
                $upload_result = handleFileUpload($_FILES['profile_image']);
                if ($upload_result['success']) {
                    $profile_image = $upload_result['path'];
                } else {
                    $error = $upload_result['message'];
                }
            }

            if (!$error) {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET full_name = ?, email = ?, phone = ?, address = ?, lat = ?, lng = ?, profile_image = ?
                    WHERE id = ?
                ");
                $stmt->execute([$full_name, $email, $phone, $address, $lat, $lng, $profile_image, $_SESSION['user_id']]);
                $success = 'Profile updated successfully.';
                
                // Refresh user data
                $user = getCurrentUser();
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } else {
            $hash = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $_SESSION['user_id']]);
            $success = 'Password changed successfully.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container page">
  <div class="profile-container">
    <div class="profile-header">
      <h1 class="page-title">Profile Settings</h1>
      <p class="page-subtitle">Manage your account information and preferences</p>
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

    <div class="profile-content">
      <!-- Profile Information -->
      <div class="profile-section">
        <div class="section-header">
          <h2>Personal Information</h2>
          <p>Update your personal details and contact information</p>
        </div>

        <form method="post" enctype="multipart/form-data" class="profile-form">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          
          <!-- Profile Image -->
          <div class="profile-image-section">
            <div class="current-image">
              <?php if ($user['profile_image']): ?>
                <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-avatar">
              <?php else: ?>
                <div class="profile-avatar-placeholder">
                  <svg width="48" height="48" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                  </svg>
                </div>
              <?php endif; ?>
            </div>
            <div class="image-upload">
              <label for="profile_image" class="upload-label">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                </svg>
                Change Photo
              </label>
              <input type="file" id="profile_image" name="profile_image" accept="image/*" class="file-input">
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label for="username" class="form-label">Username</label>
              <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" 
                     class="form-input" disabled>
              <small class="form-help">Username cannot be changed</small>
            </div>

            <div class="form-group">
              <label for="full_name" class="form-label">Full Name</label>
              <input type="text" id="full_name" name="full_name" 
                     value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" 
                     class="form-input" placeholder="Enter your full name">
            </div>

            <div class="form-group">
              <label for="email" class="form-label">Email Address</label>
              <input type="email" id="email" name="email" 
                     value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                     class="form-input" placeholder="Enter your email">
            </div>

            <div class="form-group">
              <label for="phone" class="form-label">Phone Number</label>
              <input type="tel" id="phone" name="phone" 
                     value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                     class="form-input" placeholder="Enter your phone number">
            </div>
          </div>

          <div class="form-group">
            <label for="address" class="form-label">Address</label>
            <textarea id="address" name="address" rows="3" class="form-textarea" 
                      placeholder="Enter your complete address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label for="lat" class="form-label">Latitude</label>
              <div class="location-input-wrapper">
                <input type="number" step="any" id="lat" name="lat" 
                       value="<?php echo htmlspecialchars($user['lat'] ?? ''); ?>" 
                       class="form-input" placeholder="e.g., 28.6139" readonly>
                <button type="button" id="get-location" class="location-btn">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="2"/>
                    <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
                  </svg>
                  Get My Location
                </button>
              </div>
            </div>
            <div class="form-group">
              <label for="lng" class="form-label">Longitude</label>
              <input type="number" step="any" id="lng" name="lng" 
                     value="<?php echo htmlspecialchars($user['lng'] ?? ''); ?>" 
                     class="form-input" placeholder="e.g., 77.2090" readonly>
            </div>
          </div>

          <div class="location-status" id="location-status" style="display: none;">
            <div class="status-message"></div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              Update Profile
            </button>
          </div>
        </form>
      </div>

      <!-- Password Change -->
      <div class="profile-section">
        <div class="section-header">
          <h2>Change Password</h2>
          <p>Update your password to keep your account secure</p>
        </div>

        <form method="post" class="password-form">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          <input type="hidden" name="change_password" value="1">

          <div class="form-group">
            <label for="current_password" class="form-label">Current Password</label>
            <input type="password" id="current_password" name="current_password" 
                   class="form-input" placeholder="Enter your current password" required>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label for="new_password" class="form-label">New Password</label>
              <input type="password" id="new_password" name="new_password" 
                     class="form-input" placeholder="Enter new password" required>
              <small class="form-help">Minimum 6 characters</small>
            </div>

            <div class="form-group">
              <label for="confirm_password" class="form-label">Confirm New Password</label>
              <input type="password" id="confirm_password" name="confirm_password" 
                     class="form-input" placeholder="Confirm new password" required>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-outline">
              <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
              </svg>
              Change Password
            </button>
          </div>
        </form>
      </div>

      <!-- Account Statistics -->
      <div class="profile-section">
        <div class="section-header">
          <h2>Account Statistics</h2>
          <p>Overview of your activity on AgriLease</p>
        </div>

        <div class="stats-grid">
          <?php
          // Get user statistics
          $products_stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE listed_by = ?");
          $products_stmt->execute([$_SESSION['user_id']]);
          $products_count = $products_stmt->fetchColumn();

          $bookings_stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE renter_id = ?");
          $bookings_stmt->execute([$_SESSION['user_id']]);
          $bookings_count = $bookings_stmt->fetchColumn();

          $received_bookings_stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE owner_id = ?");
          $received_bookings_stmt->execute([$_SESSION['user_id']]);
          $received_bookings_count = $received_bookings_stmt->fetchColumn();
          ?>

          <div class="stat-card">
            <div class="stat-icon">
              <svg width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
              </svg>
            </div>
            <div class="stat-content">
              <div class="stat-number"><?php echo $products_count; ?></div>
              <div class="stat-label">Products Listed</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">
              <svg width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="stat-content">
              <div class="stat-number"><?php echo $bookings_count; ?></div>
              <div class="stat-label">Bookings Made</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">
              <svg width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="stat-content">
              <div class="stat-number"><?php echo $received_bookings_count; ?></div>
              <div class="stat-label">Bookings Received</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">
              <svg width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="stat-content">
              <div class="stat-number"><?php echo timeAgo($user['created_at']); ?></div>
              <div class="stat-label">Member Since</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.profile-container {
  max-width: 800px;
  margin: 0 auto;
}

.profile-header {
  text-align: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  color: #6b7280;
  margin: 0;
  font-size: 1.1rem;
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

.profile-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.profile-section {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.section-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.section-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
}

.section-header p {
  color: #6b7280;
  margin: 0;
}

.profile-image-section {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #f3f4f6;
}

.profile-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #e5e7eb;
}

.profile-avatar-placeholder {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  border: 3px solid #e5e7eb;
}

.upload-label {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #667eea;
  color: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s;
}

.upload-label:hover {
  background: #5a67d8;
  transform: translateY(-1px);
}

.file-input {
  display: none;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
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
  font-family: inherit;
}

.form-input:focus, .form-textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input:disabled {
  background: #f9fafb;
  color: #6b7280;
  cursor: not-allowed;
}

.form-help {
  font-size: 0.8rem;
  color: #6b7280;
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
}

.form-actions {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.btn {
  display: inline-flex;
  align-items: center;
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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  background: #667eea;
  color: white;
  border-radius: 50%;
  flex-shrink: 0;
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.9rem;
  color: #6b7280;
  font-weight: 500;
}

.location-input-wrapper {
  display: flex;
  gap: 0.5rem;
  align-items: flex-end;
}

.location-input-wrapper .form-input {
  flex: 1;
}

.location-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.location-btn:hover {
  background: #059669;
  transform: translateY(-1px);
}

.location-btn:disabled {
  background: #9ca3af;
  cursor: not-allowed;
  transform: none;
}

.location-btn svg {
  flex-shrink: 0;
}

.location-status {
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
}

.location-status.success {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.location-status.error {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.location-status.loading {
  background: #dbeafe;
  color: #1e40af;
  border: 1px solid #93c5fd;
}

@media (max-width: 768px) {
  .profile-container {
    padding: 0 1rem;
  }
  
  .page-title {
    font-size: 2rem;
  }
  
  .profile-section {
    padding: 1.5rem;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .profile-image-section {
    flex-direction: column;
    text-align: center;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .stat-card {
    flex-direction: column;
    text-align: center;
  }
  
  .location-input-wrapper {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .location-btn {
    width: 100%;
    justify-content: center;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const getLocationBtn = document.getElementById('get-location');
  const latInput = document.getElementById('lat');
  const lngInput = document.getElementById('lng');
  const locationStatus = document.getElementById('location-status');
  const statusMessage = locationStatus.querySelector('.status-message');

  function showStatus(message, type) {
    statusMessage.textContent = message;
    locationStatus.className = `location-status ${type}`;
    locationStatus.style.display = 'block';
  }

  function hideStatus() {
    locationStatus.style.display = 'none';
  }

  getLocationBtn.addEventListener('click', function() {
    if (!navigator.geolocation) {
      showStatus('Geolocation is not supported by this browser.', 'error');
      return;
    }

    // Disable button and show loading
    getLocationBtn.disabled = true;
    getLocationBtn.innerHTML = `
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
        <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      Getting Location...
    `;
    showStatus('Requesting your location...', 'loading');

    const options = {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 60000
    };

    navigator.geolocation.getCurrentPosition(
      function(position) {
        const latitude = position.coords.latitude;
        const longitude = position.coords.longitude;
        const accuracy = position.coords.accuracy;

        // Update input fields
        latInput.value = latitude.toFixed(7);
        lngInput.value = longitude.toFixed(7);

        // Show success message
        showStatus(`Location captured successfully! (Accuracy: ${Math.round(accuracy)}m)`, 'success');

        // Reset button
        getLocationBtn.disabled = false;
        getLocationBtn.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="2"/>
            <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
          </svg>
          Location Updated
        `;

        // Hide status after 3 seconds
        setTimeout(hideStatus, 3000);
      },
      function(error) {
        let errorMessage = 'Unable to get your location. ';
        
        switch(error.code) {
          case error.PERMISSION_DENIED:
            errorMessage += 'Location access denied by user.';
            break;
          case error.POSITION_UNAVAILABLE:
            errorMessage += 'Location information unavailable.';
            break;
          case error.TIMEOUT:
            errorMessage += 'Location request timed out.';
            break;
          default:
            errorMessage += 'An unknown error occurred.';
            break;
        }

        showStatus(errorMessage, 'error');

        // Reset button
        getLocationBtn.disabled = false;
        getLocationBtn.innerHTML = `
          <svg width="16" height="16" viewBox="0 0="24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="2"/>
            <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
          </svg>
          Try Again
        `;
      },
      options
    );
  });
});
</script>

<?php include 'includes/footer.php'; ?>