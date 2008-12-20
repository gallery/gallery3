<? defined("SYSPATH") or die("No direct script access."); ?>
<ul>
  <li>
    <? printf(_("Operating System: %s"), PHP_OS) ?>
  </li>
  <li>
    <? printf(_("Apache: %s"), apache_get_version()) ?>
  </li>
  <li>
    <? printf(_("PHP: %s"), phpversion()) ?>
  </li>
  <li>
    <? printf(_("MySQL: %s"), mysql_get_server_info()) ?>
  </li>
</ul>
