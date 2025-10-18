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

        // handle image upload
        $image_path = null;
        if (!empty($_FILES['image']['name'])) {
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
            $success = 'Product added successfully.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container page">
  <div class="form-container">
    <div class="form-header">
      <h1 class="form-title">Add New Product</h1>
      <p class="form-subtitle">List your item for rental</p>
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
          <div class="form-group">
            <label for="title" class="form-label">Product Title *</label>
            <input id="title" name="title" class="form-input" placeholder="Enter product title" required>
          </div>
          
          <div class="form-group">
            <label for="category" class="form-label">Category</label>
            <div class="select-wrapper">
              <select id="category" name="category" class="form-input">
                <option value="General">General</option>
                <option value="Electronics">Electronics</option>
                <option value="Tools">Tools</option>
                <option value="Sports">Sports</option>
                <option value="Furniture">Furniture</option>
                <option value="Vehicles">Vehicles</option>
                <option value="Other">Other</option>
              </select>
              <svg class="select-arrow" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M8 11L3 6h10l-5 5z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="price" class="form-label">Price per day (INR) *</label>
          <div class="price-input-wrapper">
            <span class="price-prefix">₹</span>
            <input id="price" name="price" type="number" step="0.01" min="0" class="form-input price-input" placeholder="0.00" required>
          </div>
        </div>

        <div class="form-group">
          <label for="location" class="form-label">Location (City) *</label>
          <input id="location" name="location" class="form-input" placeholder="Enter your city">
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="lat" class="form-label">Latitude</label>
            <input id="lat" name="lat" class="form-input" placeholder="e.g., 28.6139">
          </div>
          <div class="form-group">
            <label for="lng" class="form-label">Longitude</label>
            <input id="lng" name="lng" class="form-input" placeholder="e.g., 77.2090">
          </div>
        </div>

        <div class="form-group">
          <label for="description" class="form-label">Description</label>
          <textarea id="description" name="description" rows="5" class="form-textarea" placeholder="Provide detailed information about your product..."></textarea>
        </div>

        <div class="form-group">
          <label for="image" class="form-label">Product Image</label>
          <div class="file-upload-wrapper">
            <input id="image" name="image" type="file" accept="image/*" class="file-input">
            <label for="image" class="file-upload-label">
              <svg class="file-upload-icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M14,13V17H10V13H7L12,8L17,13M19.35,10.03C18.67,6.59 15.64,4 12,4C9.11,4 6.6,5.64 5.35,8.03C2.34,8.36 0,10.9 0,14A6,6 0 0,0 6,20H19A5,5 0 0,0 24,15C24,12.36 21.95,10.22 19.35,10.03Z"/>
              </svg>
              <span>Choose an image</span>
            </label>
            <p class="file-help">PNG or JPG recommended. Max size: 5MB</p>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn btn-primary btn-large" type="submit">
            <svg class="btn-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            Save Product
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
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
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
}

.price-prefix {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #6b7280;
  font-weight: 600;
}

.price-input {
  padding-left: 2.5rem;
}

.file-upload-wrapper {
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  transition: all 0.2s;
}

.file-upload-wrapper:hover {
  border-color: #667eea;
  background-color: #f8fafc;
}

.file-input {
  display: none;
}

.file-upload-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  color: #6b7280;
  font-weight: 500;
  transition: color 0.2s;
}

.file-upload-label:hover {
  color: #667eea;
}

.file-upload-icon {
  color: #9ca3af;
}

.file-help {
  margin-top: 0.75rem;
  font-size: 0.875rem;
  color: #6b7280;
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
}
</style>

<?php include 'includes/footer.php'; ?>
