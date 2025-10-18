<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
if (isset($_GET['mark'])) {
    $id = (int)$_GET['mark'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    header('Location: notifications.php'); exit;
}
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notes = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<div class="container">
  <h2>Notifications</h2>
  <?php if (!$notes): ?><p>No notifications.</p><?php else: foreach ($notes as $n): ?>
    <div class="note <?php echo $n['is_read'] ? 'read' : 'unread'; ?>">
      <p><?php echo htmlspecialchars($n['message']); ?></p>
      <small><?php echo htmlspecialchars($n['created_at']); ?></small>
      <?php if (!$n['is_read']): ?><p><a href="notifications.php?mark=<?php echo $n['id']; ?>">Mark as read</a></p><?php endif; ?>
    </div>
  <?php endforeach; endif; ?>
</div>
<?php include 'includes/footer.php'; ?>