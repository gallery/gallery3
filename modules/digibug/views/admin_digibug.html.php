<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gAdminDigibug">
  <div class="gAdminDigibugIntro">
    <img id="DigibugLogo" src="<?= url::file("modules/digibug/images/digibug_logo.png") ?>">
    <h2> <?= t("Digibug Photo Printing") ?> </h2>
    <p>
      <?= t("Turn your photos into a wide variety of prints, gifts and games!") ?>
    </p>

    <ul id="gMessage">
      <li class="gSuccess">
        <?= t("You're ready to print photos!") ?>
      </li>
    </ul>

    <p>
      <?= t("You don't need an account with Digibug, but if you <a href=\"%signup_url\">register with Digibug</a> and enter your Digibug id in the <a href=\"%advanced_settings_url\">Advanced Settings</a> page you can make money off of your photos!",
          array("signup_url" => "http://www.digibug.com/signup.php",
                "advanced_settings_url" => html::mark_clean(url::site("admin/advanced_settings")))) ?>
    </p>
  </div>
</div>
