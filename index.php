<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';


if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php'); exit;
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $err = t('login_err_session');
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $err = t('login_err_empty');
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
                $err = t('login_err_invalid');
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="container page">
  <div class="form-wrap">
    <h1><?php echo t('login_title'); ?></h1>
    <p class="sub"><?php echo t('login_subtitle'); ?></p>
    <?php if ($err): ?><div class="error-message"><?php echo $err; ?></div><?php endif; ?>
    <?php if (isset($_SESSION['registration_success'])): ?>
      <div class="success-message"><?php echo t('login_success_reg'); ?></div>
      <?php unset($_SESSION['registration_success']); endif; ?>
    <?php if (isset($_GET['logged_out'])): ?>
      <div class="success-message"><?php echo t('login_success_logout'); ?></div>
    <?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <div class="form-group">
        <label for="username"><?php echo t('login_username'); ?></label>
        <input id="username" name="username" type="text" required placeholder="<?php echo t('enter_username'); ?>">
      </div>
      <div class="form-group">
        <label for="password"><?php echo t('login_password'); ?></label>
        <input id="password" name="password" type="password" required placeholder="<?php echo t('enter_password'); ?>">
      </div>
      <button class="btn" type="submit" name="login"><?php echo t('login_btn'); ?></button>
    </form>
    <p class="help"><?php echo t('login_no_account'); ?> <a href="register.php"><?php echo t('login_create'); ?></a>.</p>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
