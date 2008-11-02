<? defined("SYSPATH") or die("No direct script access."); ?>
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
<? if (empty($errors)): ?>
<p class="success">
  Your system is ready to go.
</p>
<? endif ?>
