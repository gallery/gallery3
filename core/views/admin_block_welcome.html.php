<?php defined("SYSPATH") or die("No direct script access.") ?>
<p>
  <?= t("This is your administration dashboard and it provides a quick overview of status messages, recent updates, and frequently used options. Add or remove blocks and rearrange them to tailor to your needs. The admin menu provides quick access to all of Gallery 3's options and settings. Here are a few of the most used options to get you started.") ?>
</p>
<ul>
  <li>
    <?= t('<a href="%url">General Settings</a> - General configuration options for your Gallery.',
          array("url" => "#")) ?>
  </li>
  <li>
    <?= t('<a href="%url">Modules</a> - Manage available and installed modules.',
          array("url" => url::site("admin/modules"))) ?>
  </li>
  <li>
    <?= t('<a href="">Presentation</a> - Choose a theme, set image sizes.',
          array("url" => "#")) ?>
  </li>
</ul>
