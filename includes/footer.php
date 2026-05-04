<?php ?>
<footer class="site-footer">
  <div class="container">
    <div class="brand"><span class="logo-icon">🌾</span> <?php echo t('app_name'); ?></div>
    <div class="links">
      <a href="dashboard.php"><?php echo t('nav_dashboard'); ?></a>
      <a href="add_product.php"><?php echo t('footer_add_product'); ?></a>
      <a href="my_products.php"><?php echo t('footer_my_products'); ?></a>
    </div>
    <div class="meta">&copy; <?php echo date('Y'); ?> <?php echo t('app_name'); ?>. <?php echo t('footer_rights'); ?></div>
  </div>
</footer>
</body>
</html>
