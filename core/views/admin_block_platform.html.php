<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <li>
    <?= t("Operating System: %operating_system", array("operating_system" => PHP_OS)) ?>
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
    <?= t("Server load: %load_average", array("load_average" => $load_average)) ?>
  </li>
</ul>
