<?php defined("SYSPATH") or die("No direct script access.") ?>
<h1> Uh oh! </h1>
<p class="error">
  Gallery requires at least MySQL version 5.0.0.  You're using version <?= installer::mysql_version($config) ?>
</p>
