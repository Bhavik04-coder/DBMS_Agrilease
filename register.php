<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$errors = []; $ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = t('reg_err_session');
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['confirm_password'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if ($username === '' || strlen($username) < 3) $errors[] = t('reg_err_username');
        if ($password === '' || strlen($password) < 6) $errors[] = t('reg_err_password');
        if ($password !== $confirm) $errors[] = t('reg_err_match');
        if ($email && !validateEmail($email)) $errors[] = t('reg_err_email');
        if ($phone && !validatePhone($phone)) $errors[] = t('reg_err_phone');

        if (!$errors) {

            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = t('reg_err_taken');
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
    <h1><?php echo t('reg_title'); ?></h1>
    <p class="sub"><?php echo t('reg_subtitle'); ?></p>
    <?php foreach($errors as $e): ?><div class="error-message"><?php echo $e; ?></div><?php endforeach; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <div class="form-group">
        <label for="full_name"><?php echo t('reg_full_name'); ?></label>
        <input id="full_name" name="full_name" type="text" placeholder="<?php echo t('reg_placeholder_name'); ?>">
      </div>
      <div class="form-group">
        <label for="username"><?php echo t('reg_username'); ?></label>
        <input id="username" name="username" type="text" required placeholder="<?php echo t('reg_placeholder_user'); ?>">
      </div>
      <div class="form-group">
        <label for="email"><?php echo t('reg_email'); ?></label>
        <input id="email" name="email" type="email" placeholder="<?php echo t('reg_placeholder_email'); ?>">
      </div>
      <div class="form-row two">
        <div class="form-group">
          <label for="phone"><?php echo t('reg_phone'); ?></label>
          <input id="phone" name="phone" type="tel" placeholder="<?php echo t('reg_placeholder_phone'); ?>">
        </div>
        <div class="form-group">
          <label for="password"><?php echo t('reg_password'); ?></label>
          <input id="password" name="password" type="password" required placeholder="<?php echo t('reg_placeholder_pass'); ?>">
        </div>
      </div>
      <div class="form-group">
        <label for="confirm_password"><?php echo t('reg_confirm_password'); ?></label>
        <input id="confirm_password" name="confirm_password" type="password" required placeholder="<?php echo t('reg_placeholder_conf'); ?>">
      </div>
      <button class="btn" type="submit"><?php echo t('reg_btn'); ?></button>
      <p class="help"><?php echo t('reg_have_account'); ?> <a href="index.php"><?php echo t('reg_login_link'); ?></a>.</p>
    </form>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
