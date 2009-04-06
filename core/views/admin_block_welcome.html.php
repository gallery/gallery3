<?php defined("SYSPATH") or die("No direct script access.") ?>
<p>
  <?= t("This is your administration dashboard and it provides a quick overview of status messages, recent updates, and frequently used options. Add or remove blocks and rearrange them to tailor to your needs. The admin menu provides quick access to all of Gallery 3's options and settings. Here are a few of the most used options to get you started.") ?>
</p>
<ul>
  <li>
    <?= t("General Settings - choose your <a href=\"%graphics_url\">graphics</a> and <a \"%language_url\">language</a> settings.",
        array("graphics_url" => url::site("admin/graphics"),
              "language_url" => url::site("admin/languages"))) ?>
  </li>
  <li>
    <?= t("Appearance - <a href=\"%theme_url\">choose a theme</a>, or <a href=\"%theme_details_url\">customize the way it looks</a>.",
        array("theme_url" => url::site("admin/theme"),
              "theme_details_url" => url::site("admin/theme_details"))) ?>
  </li>
  <li>
    <?= t("Customize - <a href=\"%modules_url\">install modules</a> to add cool features!",
          array("modules_url" => url::site("admin/modules"))) ?>
  </li>
</ul>
