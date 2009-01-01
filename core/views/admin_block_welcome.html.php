<?php defined("SYSPATH") or die("No direct script access.") ?>
<p>
  <?= _("This is your administration dashboard and it provides a quick overview of status messages, recent updates, and frequently used options. Add or remove blocks and rearrange them to tailor to your needs. The admin menu provides quick access to all of Gallery 3's options and settings. Here are a few of the most used options to get you started.") ?>
</p>
<ul>
  <li>
    <?= sprintf(_("%sGeneral Settings%s - General configuation options for your Gallery."), "<a href=\"#\">", "</a>") ?>
  </li>
  <li>
    <?= sprintf(_("%sModules%s - Manage available and installed modules."), "<a href=\"" . url::site("admin/modules") . "\">", "</a>") ?>
  </li>
  <li>
    <?= sprintf(_("%sPresentation%s - Choose a theme, set image sizes."), "<a href=\"#\">", "</a>") ?>
  </li>
</ul>
