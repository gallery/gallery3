<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-error">
  <h1>
    <?= t("Dang...  Page not found!") ?>
  </h1>
  <? if ($is_guest): ?>
    <h2>
      <?= t("Hey wait, you're not signed in yet!") ?>
    </h2>
    <p>
       <?= t("Maybe the page exists, but is only visible to authorized users.") ?>
       <?= t("Please sign in to find out.") ?>
    </p>
    <?= $login_form ?>
  <? else: ?>
    <p>
      <?= t("Maybe the page exists, but is only visible to authorized users.") ?>
      <?= t("Talk to your Gallery administrator if you think this is an error for help fixing this!") ?>
    </p>
 <? endif; ?>
</div>