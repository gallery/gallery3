<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-welcome-message">
  <h1 style="display: none">
    <?= t("Welcome to Gallery 3!") ?>
  </h1>

  <p>
    <h2>
      <?= t("Congratulations on choosing Gallery to host your photos.  You're going to have a great experience!") ?>
    </h2>
  </p>

  <p>
    <?= t("First things first.  You're logged in to the <b>%user_name</b> account.  You should change your password to something that you'll remember.", array("user_name" => $user->name)) ?>
  </p>

  <p>
    <a href="<?= url::site("admin/users/edit_user_form/{$user->id}") ?>"
      title="<?= t("Edit your profile")->for_html_attr() ?>"
      id="g-after-install-change-password-link"
      class="g-button ui-state-default ui-corner-all">
      <?= t("Change password and email now") ?>
    </a>
    <script type="text/javascript">
      $("#g-after-install-change-password-link").gallery_dialog();
    </script>
  </p>

  <p>
    <?= t("Want to learn more? The <a href=\"%url\">Gallery website</a> has news and information about the Gallery project and community.", array("url" => "http://galleryproject.org")) ?>
  </p>

  <p>
    <?= t("Having problems? There's lots of information in our <a href=\"%codex_url\">documentation site</a> or you can <a href=\"%forum_url\">ask for help in the forums!</a>", array("codex_url" => "http://codex.galleryproject.org/Main_Page", "forum_url" => "http://galleryproject.org/forum")) ?>
  </p>
</div>
