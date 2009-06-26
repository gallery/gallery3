<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $("#gDigibugForm").ready(function() {
    $("#gDigibugForm input:submit").parent().append('<a href="<?= url::site("admin/digibug/default_settings?csrf=$csrf") ?>" class="gDigibugDefault"><?= t("Set Default") ?></a>');
  });
</script>
<div id="gAdminDigibug">
  <div class="gAdminDigibugIntro">
    <p>
      <?= t("allows you to turn your photos into a wide variety of prints, gifts and games.") ?>
    </p>
  </div>
  <div id="gDigibugAccount">
    <div style="width: 120px;" class="gDigibugText gDigibugSignIn">
      <?= t("Don't have an account?") ?>
      <br/> <br/>
      <a href="http://www.digibug.com/signup.php" style=""><?= t("Sign up") ?></a><?= t(" to get started") ?>
    </div>
      <?= $form ?>
  </div>
</div>
