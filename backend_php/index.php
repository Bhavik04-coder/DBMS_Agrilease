<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';


if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php'); exit;
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $err = 'Invalid session. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $err = 'Please enter username and password.';
        } else {
            $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: ' . (!empty($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'dashboard.php'));
                unset($_SESSION['redirect_url']);
                exit;
            } else {
                $err = 'Invalid credentials.';
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="container page">
  <div class="form-wrap">
    <h1>Welcome to AgriLease</h1>
    <p class="sub">Sign in to manage your listings and bookings.</p>
    <?php if ($err): ?><div class="error-message"><?php echo $err; ?></div><?php endif; ?>
    <?php if (isset($_SESSION['registration_success'])): ?>
      <div class="success-message">Registration successful. Please log in.</div>
      <?php unset($_SESSION['registration_success']); endif; ?>
    <?php if (isset($_GET['logged_out'])): ?>
      <div class="success-message">You have been logged out successfully.</div>
    <?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <div class="form-group">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" required placeholder="Enter username">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required placeholder="Enter password">
      </div>
      <button class="btn" type="submit" name="login">Log in</button>
    </form>
    <p class="help">Don't have an account? <a href="register.php">Create one</a>.</p>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
