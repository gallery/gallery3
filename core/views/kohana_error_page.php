<? defined("SYSPATH") or die("No direct script access.") ?>
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
        width: 42em;
        margin: 20px auto;
      }

      div#framework_error {
        text-align: center;
      }

      div#error_details {
        text-align: left;
      }

      code {
        font-family: monospace;
        font-size: 12px;
        margin: 20px;
        color: #333;
        white-space: pre-wrap;
        white-space: -moz-pre-wrap;
        word-wrap: break-word;
      }

      h3 {
        font-family: sans-serif;
        margin: 2px 0px 0px 0px;
        padding: 8px 0px 0px 0px;
        border-top: 1px solid #ddd;
      }

      p {
        padding: 0px;
        margin: 0px 0px 10px 0px;
      }

      li, pre {
        padding: 0px;
        margin: 0px;
      }
    </style>
    <script src="<?= url::file("lib/jquery.js") ?>" type="text/javascript"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?= t("Something went wrong!") ?></title>
  </head>
  <body>
    <? try { $user = user::active(); } catch (Exception $e) { } ?>
    <? $admin = isset($user) && $user->admin ?>
    <div class="big_box" id="framework_error">
      <h1>
        <?= t("Dang...  Something went wrong!") ?>
      </h1>
      <h2>
        <?= t("We tried really hard, but it's broken.") ?>
      </h2>
      <? if (!$admin): ?>
      <p>
        <?= t("Talk to your Gallery administrator for help fixing this!") ?>
      </p>
      <? endif ?>
    </div>
    <? if ($admin): ?>
    <div class="big_box" id="error_details">
      <h2>
        <?= t("Hey wait, you're an admin!  We can tell you stuff.") ?>
      </h2>
      <a id="toggle" href=""
         onclick="javascript:$('#stuff').slideDown('slow'); $('#toggle').slideUp(); return false">
        <b><?= t("Ok.. tell me stuff!") ?></b>
      </a>
      <div id="stuff" style="display: none">
        <? if (!empty($line) and !empty($file)): ?>
        <div id="summary">
          <h3>
            <?= t("Help!") ?>
          </h3>
          <p>
            <?= t("If this stuff doesn't make any sense to you, <a href=\"%url\">ask for help in the Gallery forums</a>!", array("url" => "http://gallery.menalto.com/forum/96")) ?>
          </p>
          <h3>
            <?= t("So here's the error:") ?>
          </h3>

          <code class="block"><?= $message ?></code>
          <p>
            <?= t("File: <b>%file</b>, line: <b>%line</b>", array("file" => $file, "line" => $line)) ?>
          </p>
        </div>
        <? endif ?>

        <? $trace = $PHP_ERROR ? array_slice(debug_backtrace(), 1) : $exception->getTrace(); ?>
        <? $trace = Kohana::backtrace($trace); ?>
        <? if (!empty($trace)): ?>
        <div id="stack_trace">
          <h3>
            <?= t("And here's how we got there:") ?>
          </h3>
          <?= $trace ?>
          <? endif ?>
        </div>
      </div>
      <? endif ?>
  </body>
</html>
