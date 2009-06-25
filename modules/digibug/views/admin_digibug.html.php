<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $("#gDigibugTabs").ready(function() {
    $("#gDigibugTabs").tabs({});
  });
</script>
<div id="gAdminDigibug">
  <div class="gAdminDigibugIntro">
    <p>
      <?= t("offers you two options for turning your photos into a wide variety of prints, gifts and games. Choose your solution and get started today!") ?>
    </p>
  </div>
  <div id="gDigibugTabs">
    <ul>
      <li><a href="#gDigibugTabBasic"><?= t("Basic") ?></a></li>
      <li><a href="#gDigibugTabAdvanced"><?= t("Advanced") ?></a></li>
    </ul>
    <div id="gDigibugTabBasic" class="gDigibugTab">
      <div class="gDigibugText">
        <?= t("Use Digibug Basic and there's nothing else to do - no registration, no administration.") ?>
      </div>
      <div class="gDigibugRounded ui-corner-all">
        <br/>
        <? if ($mode == "basic"): ?>
        <?= t("You are currently using Basic mode!") ?>
        <? else: ?>
        <a href='<?= url::site("admin/digibug/basic?csrf=$csrf") ?>'><?= t("Click Here") ?></a>
           <?= t(" to switch back to basic") ?>
        <? endif ?>
      </div>
    </div>
    <div id="gDigibugTabAdvanced" class="gDigibugTab">
      <div class="gDigibugText">
        <?= t("Digibug Advanced allows you to set your own price for photos and gifts. Simply provide your account information.") ?>
      </div>
      <div style="width: 120px;" class="gDigibugText gDigibugSignIn">
        <?= t("New to Digibug ADVANCED?") ?>
        <br/> <br/>
        <a href="http://www.digibug.com/signup.php" style=""><?= t("Sign up") ?></a><?= t(" to get started") ?>
      </div>
      <div class="gDigibugAdvancedForm">
        <div class="gDigibugText"><?= t("Enter your Digibug company ID and event ID") ?></div>
        <?= $form ?>
      </div>
    </div>
  </div>
</div>
