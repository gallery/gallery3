<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <li>
    <? printf(_("Operating System: %s"), PHP_OS) ?>
  </li>
  <li>
    <? printf(_("Apache: %s"), function_exists("apache_get_version") ? apache_get_version() : _("Unknown")) ?>
  </li>
  <li>
    <? printf(_("PHP: %s"), phpversion()) ?>
  </li>
  <li>
    <? printf(_("MySQL: %s"), mysql_get_server_info()) ?>
  </li>
</ul>
