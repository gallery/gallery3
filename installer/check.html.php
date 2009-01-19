<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title>Gallery3 Installer: System Checks</title>
    <link rel="stylesheet" type="text/css" href="install.css"/>
  </head>
  <body>
    <div id="outer">
      <div id="inner">
        <img width="300" height="178" src="../core/images/gallery2.png"/>

        <h1> System Checks </h1>
        <?php if (installer::already_installed()): ?>
        <p>
          As a privacy measure, we do not allow you to run the system
          check page after your Gallery has been installed.  This
          keeps prying eyes from learning about your system.
        </p>
        <?php else: ?>

        <?php ob_start() ?>
        <ul class="errors">
          <?php if (version_compare(PHP_VERSION, "5.2", "<")): ?>
          <li>
            Gallery 3 requires PHP 5.2 or newer, current version: <?= PHP_VERSION ?>
          </li>
          <?php $fail++; endif ?>

          <?php if (!function_exists("mysql_query") && !function_exists("mysqli_init")): ?>
          <li>
            Gallery 3 requires a MySQL database, but PHP doesn't have either the
            the <a href="http://php.net/mysql">MySQL</a>
            or the  <a href="http://php.net/mysqli">MySQLi</a> extension.
          </li>
          <?php $fail++; endif ?>

          <?php if (!@preg_match("/^.$/u", utf8_encode("\xF1"))): ?>
          <li>
            PHP is missing <a href="http://php.net/pcre">Perl-Compatible Regular Expression</a> support.
          </li>
          <?php $fail++; endif ?>

          <?php if (!(class_exists("ReflectionClass"))): ?>
          <li>
            PHP is missing <a href="http://php.net/reflection">reflection</a> support
          </li>
          <?php $fail++; endif ?>

          <?php if (!(function_exists("filter_list"))): ?>
          <li>
            PHP is missing the <a href="http://php.net/filter">filter extension</a>
          </li>
          <?php $fail++; endif ?>

          <?php if (!(extension_loaded("iconv"))): ?>
          <li>
            PHP is missing the <a href="http://php.net/iconv">iconv extension</a>
          </li>
          <?php $fail++; endif ?>

          <?php if (extension_loaded("mbstring") && (ini_get("mbstring.func_overload") & MB_OVERLOAD_STRING)): ?>
          <li>
            The <a href="http://php.net/mbstring">mbstring
            extension</a> is overloading PHP's native string
            functions.  Please disable it.
          </li>
          <?php endif ?>
        </ul>
        <?php $errors = ob_get_clean() ?>

        <?php if (!empty($fail)): ?>
        <p>
          There are some problems with your web hosting environment
          that need to be fixed before you can successfully install
          Gallery 3.
        </p>
        <?php echo $errors ?>
        <p>
          <a href="check.php">Check again</a>
        </p>

        <?php else: ?>
        <p>
          Good news!  We've checked everything we can think of and it
          looks like Gallery 3 should work just fine on your system.
        </p>
        <? endif ?>
        <? endif /* already_installed check */?>
      </div>
    </div>
  </body>
</html>
