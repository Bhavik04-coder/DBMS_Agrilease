<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$errors = []; $ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid session. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['confirm_password'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if ($username === '' || strlen($username) < 3) $errors[] = 'Username must be at least 3 characters.';
        if ($password === '' || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';
        if ($email && !validateEmail($email)) $errors[] = 'Invalid email.';
        if ($phone && !validatePhone($phone)) $errors[] = 'Phone must be 10-15 digits.';

        if (!$errors) {

            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = 'Username already taken.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone) VALUES (?,?,?,?,?)");
                $stmt->execute([$username, $hash, $full_name, $email, $phone]);
                $_SESSION['registration_success'] = true;
                header('Location: index.php'); exit;
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="container page">
  <div class="form-wrap">
    <h1>Create account</h1>
    <p class="sub">Join AgriLease to list and rent farm equipment.</p>
    <?php foreach($errors as $e): ?><div class="error-message"><?php echo $e; ?></div><?php endforeach; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <div class="form-group">
        <label for="full_name">Full name</label>
        <input id="full_name" name="full_name" type="text" placeholder="Your name">
      </div>
      <div class="form-group">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" required placeholder="Choose a username">
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" placeholder="you@example.com">
      </div>
      <div class="form-row two">
        <div class="form-group">
          <label for="phone">Phone</label>
          <input id="phone" name="phone" type="tel" placeholder="10-15 digits">
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required placeholder="At least 6 characters">
        </div>
      </div>
      <div class="form-group">
        <label for="confirm_password">Confirm password</label>
        <input id="confirm_password" name="confirm_password" type="password" required placeholder="Repeat password">
      </div>
      <button class="btn" type="submit">Create account</button>
      <p class="help">Already have an account? <a href="index.php">Log in</a>.</p>
    </form>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
