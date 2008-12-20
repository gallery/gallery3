<? defined("SYSPATH") or die("No direct script access."); ?>
<ul>
  <li>
    Operating System: <?= PHP_OS ?>
  </li>
  <li>
    Apache: <?= apache_get_version() ?>
  </li>
  <li>
    PHP <?= phpversion() ?>
  </li>
  <li>
    MySQL: <?= mysql_get_server_info() ?>
  </li>
</ul>
