<?php defined("SYSPATH") or die("No direct script access.") ?>
<p>
  <?= t("This is your administration dashboard and it provides a quick overview of status messages, recent updates, and frequently used options. Add or remove blocks and rearrange them to tailor to your needs. The admin menu provides quick access to all of Gallery 3's options and settings. Here are a few of the most used options to get you started.") ?>
</p>
<ul>
  <li>
    <?= t("%link_startGeneral Settings%link_end - General configuation options for your Gallery.",
          array("link_start" => "<a href=\"#\">", "link_end" => "</a>")) ?>
  </li>
  <li>
    <?= t("%link_startModules%link_end - Manage available and installed modules.",
          array("link_start" => "<a href=\"" . url::site("admin/modules") . "\">", "link_end" => "</a>")) ?>
  </li>
  <li>
    <?= t("%link_startPresentation%link_end - Choose a theme, set image sizes.",
          array("link_start" => "<a href=\"#\">", "link_end" => "</a>")) ?>
  </li>
</ul>
