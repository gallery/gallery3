<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <li>
    <?= t("Host name: %host_name", array("host_name" => php_uname("n"))) ?>
  </li>
  <li>
    <?= t("Operating system: %os %version", array("os" => php_uname("s"), "version" => php_uname("r"))) ?>
  </li>
  <li>
    <?= t("Apache: %apache_version", array("apache_version" => function_exists("apache_get_version") ? apache_get_version() : t("Unknown"))) ?>
  </li>
  <li>
    <?= t("PHP: %php_version", array("php_version" => phpversion())) ?>
  </li>
  <li>
    <?= t("MySQL: %mysql_version", array("mysql_version" => Database::instance()->query("SELECT version() as v")->current()->v)) ?>
  </li>
  <li>
    <?= t("Server load: %load_average", array("load_average" => join(" ", sys_getloadavg()))) ?>
  </li>
  <li>
    <?= t("Graphics toolkit: %toolkit", array("toolkit" => module::get_var("gallery", "graphics_toolkit"))) ?>
  </li>
</ul>
