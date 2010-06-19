<?php defined("SYSPATH") or die("No direct script access.") ?>
<? if (!function_exists("t")) { function t($msg) { return $msg; } } ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <style type="text/css">
      body {
        background: #fff;
        font-size: 14px;
        line-height: 130%;
      }

      div.big_box {
        padding: 10px;
        background: #eee;
        border: solid 1px #ccc;
        font-family: sans-serif;
        color: #111;
        width: 60em;
        margin: 20px auto;
      }

      div#framework_error {
        text-align: center;
      }
    </style>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?= t("Something went wrong!") ?></title>
  </head>
  <body>
    <div class="big_box" id="framework_error">
      <h1>
        <?= t("Dang...  Something went wrong!") ?>
      </h1>
      <h2>
        <?= t("We tried really hard, but it's broken.") ?>
      </h2>
      <p>
        <?= t("Talk to your Gallery administrator for help fixing this!") ?>
      </p>
    </div>
  </body>
</html>
