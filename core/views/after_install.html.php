<?php defined("SYSPATH") or die("No direct script access.") ?>
<h1 style="display: none">
  <?= t("Welcome to Gallery 3!") ?>
</h1>

<p>
  <?= t("Congratulations on choosing Gallery to host your photos.  We're confident that you're going to have a great experience.") ?>
</p>

<p>
  <?= t("You're logged in to the <b>%user_name</b> account.  The very first thing you should do is to change your password to something that you'll remember.", array("user_name" => $user->name)) ?>
</p>

<p>
  <a href="<?= url::site("form/edit/users/{$user->id}") ?>"
    title="<?= t("Edit Your Profile") ?>"
    id="gAfterInstallChangePasswordLink" class="gButtonLink ui-state-default ui-corners-all"><?= t("Change Password Now") ?></a>
  <script>
    $("#gAfterInstallChangePasswordLink").bind("click", handleDialogEvent);
  </script>
</p>

<p>
  <?= t("Want to learn more? The <a href=\"%url\">Gallery website</a> has news and information about Gallery Project and community.", array("url" => "http://gallery.menalto.com")) ?>
</p>

<p>
  <?= t("Having problems? There's lots of information in our <a href=\"%codex_url\">documentation site</a> or you can <a href=\"%forum_url\">ask for help in the forums!</a>", array("codex_url" => "http://codex.gallery2.org/Main_Page", "forum_url" => "http://gallery.menalto.com/forum")) ?>
</ul>
