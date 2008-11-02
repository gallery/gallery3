<? defined("SYSPATH") or die("No direct script access."); ?>
<html>
  <head>
    <title>Gallery3 Scaffold</title>
    <style>
      body {
      font-family: Trebuchet MS;
      }

      p {
        margin: 0 0 0 0;
        padding: 5px;
      }

      pre {
        margin: 0;
        padding-left: 1em;
      }

      .error {
        color: red;
      }

      div.block {
        border: 1px solid black;
        margin-bottom: 5px;
        paddding: 1em;
      }
    </style>
  </head>
  <body>
    <? foreach ($errors as $error): ?>
    <div class="block">
      <p class="error">
	<?= $error->message ?>
      </p>
      <? foreach ($error->instructions as $line): ?>
      <pre><?= $line ?></pre>
      <? endforeach ?>

      <? if (!empty($error->message2)): ?>
      <p class="error">
	<?= $error->message2 ?>
      </p>
      <? endif ?>
    </div>
    <? endforeach ?>
  </body>
</html>
