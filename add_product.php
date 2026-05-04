<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$error=''; $success='';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session. Please try again.';
    } else {
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $category = sanitizeInput($_POST['category'] ?? 'General');
        $location = sanitizeInput($_POST['location'] ?? '');
        $lat = is_numeric($_POST['lat'] ?? null) ? (float)$_POST['lat'] : null;
        $lng = is_numeric($_POST['lng'] ?? null) ? (float)$_POST['lng'] : null;

        // Validate required fields
        if (empty($title)) {
            $error = t('add_err_title');
        } elseif ($price <= 0) {
            $error = t('add_err_price');
        } elseif (empty($location)) {
            $error = t('add_err_location');
        } elseif ($lat !== null && ($lat < -90 || $lat > 90)) {
            $error = t('add_err_lat');
        } elseif ($lng !== null && ($lng < -180 || $lng > 180)) {
            $error = t('add_err_lng');
        }

        // handle image upload
        $image_path = null;
        if (!$error && !empty($_FILES['image']['name'])) {
            $res = handleFileUpload($_FILES['image']);
            if ($res['success']) {
                $image_path = $res['path'];
            } else {
                $error = $res['message'];
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare("INSERT INTO products (title, description, category, price, image_path, location, lat, lng, listed_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $description, $category, $price, $image_path, $location, $lat, $lng, $_SESSION['user_id']]);
            $success = t('add_success');
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container page">
  <div class="form-container">
    <div class="form-header">
      <h1 class="form-title"><?php echo t('add_title'); ?></h1>
      <p class="form-subtitle"><?php echo t('add_subtitle'); ?></p>
    </div>
    
    <div class="form-content">
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

      <form method="post" enctype="multipart/form-data" class="product-form">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        
        <div class="form-grid">
          <div class="form-group full-width">
            <label for="title" class="form-label">
              <svg class="label-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M20 7h-9M14 17H5M17 12H3" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <?php echo t('add_prod_title'); ?>
            </label>
            <input id="title" name="title" class="form-input" placeholder="<?php echo t('add_prod_title_ph'); ?>" required>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="category" class="form-label">
              <svg class="label-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="3" y="3" width="7" height="7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="14" y="3" width="7" height="7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="14" y="14" width="7" height="7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="3" y="14" width="7" height="7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <?php echo t('add_category'); ?>
            </label>
            <div class="select-wrapper">
              <select id="category" name="category" class="form-input" required>
                <option value=""><?php echo t('add_select_cat'); ?></option>
                <option value="Tractors"><?php echo t('cat_tractors'); ?></option>
                <option value="Harvesters"><?php echo t('cat_harvesters'); ?></option>
                <option value="Tillers"><?php echo t('cat_tillers'); ?></option>
                <option value="Seeders"><?php echo t('cat_seeders'); ?></option>
                <option value="Sprayers"><?php echo t('cat_sprayers'); ?></option>
                <option value="Irrigation"><?php echo t('cat_irrigation'); ?></option>
                <option value="Trailers"><?php echo t('cat_trailers'); ?></option>
                <option value="Other"><?php echo t('cat_other'); ?></option>
              </select>
              <svg class="select-arrow" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M8 11L3 6h10l-5 5z"/>
              </svg>
            </div>
          </div>

          <div class="form-group">
            <label for="price" class="form-label">
              <svg class="label-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <?php echo t('add_price'); ?>
            </label>
            <div class="price-input-wrapper">
              <span class="price-prefix">₹</span>
              <input id="price" name="price" type="number" step="0.01" min="0" class="form-input price-input" placeholder="<?php echo t('add_price_ph'); ?>" required>
              <span class="price-suffix">/<?php echo ($_SESSION['lang'] ?? 'en') === 'mr' ? 'दिवस' : 'day'; ?></span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="location" class="form-label">
            <svg class="label-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-width="2"/>
              <circle cx="12" cy="10" r="3" stroke-width="2"/>
            </svg>
            <?php echo t('add_location'); ?>
          </label>
          <input id="location" name="location" class="form-input" placeholder="<?php echo t('add_location_ph'); ?>" required>
        </div>

        <div class="location-section">
          <div class="location-header">
            <label class="form-label">
              <svg class="label-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z" stroke-width="2"/>
                <circle cx="12" cy="10" r="3" stroke-width="2"/>
              </svg>
              <?php echo t('add_gps'); ?>
            </label>
            <button type="button" id="get-location" class="location-btn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                <path d="M12 16v-4M12 8h.01" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <?php echo t('add_auto_detect'); ?>
            </button>
          </div>
          
          <div class="form-grid">
            <div class="form-group">
              <label for="lat" class="form-label-small"><?php echo t('add_latitude'); ?></label>
              <input id="lat" name="lat" class="form-input" placeholder="28.6139" readonly>
            </div>
            <div class="form-group">
              <label for="lng" class="form-label-small"><?php echo t('add_longitude'); ?></label>
              <input id="lng" name="lng" class="form-input" placeholder="77.2090" readonly>
            </div>
          </div>

          <div class="location-status" id="location-status" style="display: none;">
            <div class="status-message"></div>
          </div>
        </div>

        <div class="form-group">
          <label for="description" class="form-label">
            <svg class="label-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke-width="2"/>
              <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <?php echo t('add_description'); ?>
          </label>
          <textarea id="description" name="description" rows="5" class="form-textarea" placeholder="<?php echo t('add_desc_ph'); ?>"></textarea>
          <p class="field-help"><?php echo t('add_desc_help'); ?></p>
        </div>

        <div class="form-group">
          <label for="image" class="form-label">
            <svg class="label-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke-width="2"/>
              <circle cx="8.5" cy="8.5" r="1.5" stroke-width="2"/>
              <path d="M21 15l-5-5L5 21" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <?php echo t('add_image'); ?>
          </label>
          <div class="file-upload-wrapper" id="file-upload-wrapper">
            <input id="image" name="image" type="file" accept="image/*" class="file-input">
            <label for="image" class="file-upload-label">
              <div class="upload-icon-wrapper">
                <svg class="file-upload-icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
              <span class="upload-text"><?php echo t('add_upload_text'); ?></span>
              <span class="upload-subtext"><?php echo t('add_upload_sub'); ?></span>
            </label>
            <div id="image-preview" class="image-preview" style="display: none;">
              <img id="preview-img" src="" alt="Preview">
              <button type="button" id="remove-image" class="remove-image-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M18 6L6 18M6 6l12 12" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn btn-primary btn-large" type="submit">
            <svg class="btn-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <?php echo t('add_save_btn'); ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.form-container {
  max-width: 800px;
  margin: 2rem auto;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.form-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
  text-align: center;
}

.form-title {
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.form-subtitle {
  opacity: 0.9;
  margin: 0;
  font-size: 1.1rem;
}

.form-content {
  padding: 2rem;
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

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.form-label-small {
  display: block;
  font-weight: 500;
  color: #6b7280;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.label-icon {
  flex-shrink: 0;
  color: #667eea;
}

.field-help {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #6b7280;
  line-height: 1.4;
}

.form-input, .form-textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.2s;
  background: white;
}

.form-input:focus, .form-textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 120px;
}

.select-wrapper {
  position: relative;
}

.select-arrow {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: #6b7280;
}

.price-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.price-prefix {
  position: absolute;
  left: 1rem;
  color: #667eea;
  font-weight: 700;
  font-size: 1.1rem;
}

.price-suffix {
  position: absolute;
  right: 1rem;
  color: #6b7280;
  font-weight: 500;
  font-size: 0.9rem;
}

.price-input {
  padding-left: 2.5rem;
  padding-right: 4rem;
}

.file-upload-wrapper {
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  padding: 2.5rem;
  text-align: center;
  transition: all 0.3s;
  background: #f9fafb;
  position: relative;
}

.file-upload-wrapper:hover {
  border-color: #667eea;
  background-color: #f0f4ff;
  transform: translateY(-2px);
}

.file-input {
  display: none;
}

.file-upload-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
  transition: all 0.2s;
}

.upload-icon-wrapper {
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  border-radius: 50%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s;
}

.file-upload-wrapper:hover .upload-icon-wrapper {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.file-upload-icon {
  color: #667eea;
}

.upload-text {
  font-weight: 600;
  color: #374151;
  font-size: 1rem;
}

.upload-subtext {
  font-size: 0.875rem;
  color: #6b7280;
}

.image-preview {
  position: relative;
  margin-top: 1rem;
  border-radius: 8px;
  overflow: hidden;
  max-width: 300px;
  margin-left: auto;
  margin-right: auto;
}

.image-preview img {
  width: 100%;
  height: auto;
  display: block;
  border-radius: 8px;
}

.remove-image-btn {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: rgba(239, 68, 68, 0.9);
  color: white;
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.remove-image-btn:hover {
  background: #dc2626;
  transform: scale(1.1);
}

.form-actions {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
  text-align: center;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 2rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-large {
  padding: 1rem 2.5rem;
  font-size: 1.1rem;
}

.btn-icon {
  flex-shrink: 0;
}

.location-section {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.location-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.location-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
  box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
}

.location-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.location-btn:disabled {
  background: #9ca3af;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
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
  .form-container {
    margin: 1rem;
    border-radius: 8px;
  }
  
  .form-content {
    padding: 1.5rem;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .form-header {
    padding: 1.5rem;
  }
  
  .form-title {
    font-size: 1.75rem;
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
var _lang = {
  gettingLoc:   <?php echo json_encode(t('add_getting_loc')); ?>,
  locCaptured:  <?php echo json_encode(t('add_loc_captured')); ?>,
  tryAgain:     <?php echo json_encode(t('add_try_again')); ?>,
  requesting:   <?php echo json_encode(t('add_loc_requesting')); ?>,
  locSuccess:   <?php echo json_encode(t('add_loc_success')); ?>,
  locDenied:    <?php echo json_encode(t('add_loc_denied')); ?>,
  locUnavail:   <?php echo json_encode(t('add_loc_unavail')); ?>,
  locTimeout:   <?php echo json_encode(t('add_loc_timeout')); ?>,
  locUnknown:   <?php echo json_encode(t('add_loc_unknown')); ?>,
  locUnsupported: <?php echo json_encode(t('add_loc_unsupported')); ?>
};

document.addEventListener('DOMContentLoaded', function() {
  // Image preview functionality
  const imageInput = document.getElementById('image');
  const imagePreview = document.getElementById('image-preview');
  const previewImg = document.getElementById('preview-img');
  const removeImageBtn = document.getElementById('remove-image');
  const fileUploadLabel = document.querySelector('.file-upload-label');

  imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        previewImg.src = e.target.result;
        imagePreview.style.display = 'block';
        fileUploadLabel.style.display = 'none';
      };
      reader.readAsDataURL(file);
    }
  });

  removeImageBtn.addEventListener('click', function() {
    imageInput.value = '';
    imagePreview.style.display = 'none';
    fileUploadLabel.style.display = 'flex';
  });

  // Location functionality
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
      showStatus(_lang.locUnsupported, 'error');
      return;
    }

    // Disable button and show loading
    getLocationBtn.disabled = true;
    getLocationBtn.innerHTML = `
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
        <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      ${_lang.gettingLoc}
    `;
    showStatus(_lang.requesting, 'loading');

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
        showStatus(_lang.locSuccess.replace('{acc}', Math.round(accuracy)), 'success');

        // Reset button
        getLocationBtn.disabled = false;
        getLocationBtn.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M20 6L9 17l-5-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          ${_lang.locCaptured}
        `;

        // Hide status after 3 seconds
        setTimeout(hideStatus, 3000);
      },
      function(error) {
        let errorMessage = '';
        
        switch(error.code) {
          case error.PERMISSION_DENIED:
            errorMessage = _lang.locDenied;
            break;
          case error.POSITION_UNAVAILABLE:
            errorMessage = _lang.locUnavail;
            break;
          case error.TIMEOUT:
            errorMessage = _lang.locTimeout;
            break;
          default:
            errorMessage = _lang.locUnknown;
            break;
        }

        showStatus(errorMessage, 'error');

        // Reset button
        getLocationBtn.disabled = false;
        getLocationBtn.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="12" cy="12" r="10" stroke-width="2"/>
            <path d="M12 16v-4M12 8h.01" stroke-width="2" stroke-linecap="round"/>
          </svg>
          ${_lang.tryAgain}
        `;
      },
      options
    );
  });

  // Auto-get location on page load (optional)
  // Uncomment the next line if you want to automatically request location when page loads
  // getLocationBtn.click();
});
</script>

<?php include 'includes/footer.php'; ?>
