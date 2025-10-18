<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header('Location: dashboard.php'); exit; }
$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND listed_by = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$product = $stmt->fetch();
if (!$product) { header('Location: dashboard.php'); exit; }

$error=''; $success='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session. Please try again.';
    } else {
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $category = sanitizeInput($_POST['category'] ?? 'General');

        if ($title === '' || $price <= 0) {
            $error = 'Please enter a title and a valid price.';
        } else {
            $image_path = $product['image_path'];
            if (!empty($_FILES['image']['name'])) {
                if (!is_dir('assets/images')) mkdir('assets/images', 0777, true);
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $fname = 'prod_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . strtolower($ext);
                $dest = 'assets/images/' . $fname;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $image_path = $dest;
                }
            }
            $stmt = $pdo->prepare("UPDATE products SET title=?, description=?, price=?, category=?, image_path=? WHERE id=? AND listed_by=?");
            $stmt->execute([$title, $description, $price, $category, $image_path, $id, $_SESSION['user_id']]);
            $success = 'Product updated.';
            // refresh data
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND listed_by = ?");
            $stmt->execute([$id, $_SESSION['user_id']]);
            $product = $stmt->fetch();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="container page">
  <div class="form-wrap">
    <h1>Edit Product</h1>
    <?php if ($error): ?><div class="error-message"><?php echo $error; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success-message"><?php echo $success; ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <div class="form-group">
        <label for="title">Title</label>
        <input id="title" name="title" type="text" required value="<?php echo htmlspecialchars($product['title']); ?>">
      </div>
      <div class="form-group">
        <label for="category">Category</label>
        <input id="category" name="category" type="text" value="<?php echo htmlspecialchars($product['category']); ?>">
      </div>
      <div class="form-group">
        <label for="price">Price per day (₹)</label>
        <input id="price" name="price" type="number" step="0.01" min="0" required value="<?php echo htmlspecialchars($product['price']); ?>">
      </div>
      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
      </div>
      <div class="form-group">
        <label for="image">Image (leave blank to keep current)</label>
        <input id="image" name="image" type="file" accept="image/*">
      </div>
      <button class="btn" type="submit">Update</button>
    </form>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
