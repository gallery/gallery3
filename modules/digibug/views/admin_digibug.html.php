<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $("#gDigibugTabs").ready(function() {
    $("#gDigibugTabs").tabs({
      event: "mouseover"
    });
  });
</script>
<div id="gAdminDigibug">
  <h2><?= t("Digibug Adminstration") ?>  </h2>
  <div class="gAdminDigibugIntro">
    <p>
      <?= t("Digibug offers you two options for turning your photos into a wide variety of prints, gifts and games. Choose your solution and get started today!") ?>
    </p>
  </div>
  <div id="gDigibugTabs">
    <ul>
      <li><a href="#gDigibugTabBasic"><?= t("Basic") ?></a></li>
      <li><a href="#gDigibugTabAdvanced"><?= t("Advanced") ?></a></li>
    </ul>
    <div id="gDigibugTabBasic" class="gDigibugTab">
      <div class="gDigibugTitle">
        <?= t("Digibug Basic") ?>
        <br/>
        <?= t("Fast Easy Photo Fulfillment") ?>
      </div>
      <div class="gDigibugText">
        <?= t("Power up your Gallery with professional level fulfillment from Kodak. Just use Digibug Basic and there's nothing else to do - no registration, no administration, no hassles.") ?>
      </div>
      <div class="gDigibugListItems">
        <ul>
          <li><?= t("Matte and Glossy prints, from 4x6 to as big as 30x40") ?></li>
          <li><?= t("Great photo gifts like canvases, apparel, bags, puzzles, mugs and sports memorabilia and more") ?></li>
          <li><?= t("Outstanding quality and customer service") ?></li>
        </ul>
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
      <div class="gDigibugTitle">
        <?= t("Digibug ADVANCED") ?>
        <br/>
        <?= t("The Pro\'s Solution") ?>
      </div>
      <div class="gDigibugText">
        <?= t("Digibug ADVANCED allows you to set your own price for photos and gifts. Simply provide us with your account information and we'll send you a check each month with your profits. It's the perfect online retail business solution for a photographer - no inventory, no overhead... just profits!") ?>
      </div>
      <div class="gDigibugText">
        <?= t("Enjoy the same range of professional level photo prints and gifts, but set your own price and charge what you believe your photos are worth. We'll take care of the rest.") ?>
      </div>
      <div style="width: 120px;" class="gDigibugText gDigibugSignIn">
        <?= t("New to Digibug ADVANCED?") ?>
        <br/> <br/>
        <a href="http://www.digibug.com/signup.php" style=""><?= t("Sign up") ?></a><?= t(" to get started") ?>
      </div>
      <div class="gDigibugAdvancedForm">
        <div class="gDigibugText"><?= t("Do you have a Digibug Company ID and Event ID?") ?></div>
        <?= $form ?>
      </div>
    </div>
  </div>
</div>
