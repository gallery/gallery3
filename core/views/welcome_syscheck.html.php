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
<div class="block">
  <? if (empty($modules['core'])): ?>
  <p class="success">
    Your system is ready, but Gallery is not yet installed.
  </p>
  <p>
    <?= html::anchor("welcome/install/core", "install gallery") ?>
  </p>
  <? else: ?>
  <p class="success">
    Your Gallery is ready with the following modules installed:
  </p>
  <table style="width: 400px">
    <tr>
      <th align="left">Name</th>
      <th align="left">Version</th>
      <th align="left">Action</th>
    </tr>
    <tr class="core">
      <td> <b> core </b> </td>
      <td> <b> <?= $modules["core"] ?> </b> </td>
      <td> <b> <?= html::anchor("welcome/uninstall/core", "reset install") ?> </b> </td>
    </tr>
    <? foreach ($modules as $module_name => $module_version): ?>
    <? if ($module_name == "core") continue; ?>
    <tr>
      <td><?= $module_name ?></td>
      <td><?= empty($module_version) ? "" : $module_version ?></td>
      <td>
        <? if (empty($module_version)): ?>
          <?= html::anchor("welcome/install/{$module_name}", "install") ?>
        <? else: ?>
          <?= html::anchor("welcome/uninstall/{$module_name}", "uninstall") ?>
        <? endif; ?>
      </td>
    </tr>
    <? endforeach; ?>
    <tr>
      <td colspan="3" align="center">
        <button onclick="document.location.href='<?= url::site("welcome/install/*") ?>'">Install All Plugins</button>
      </td>
    </tr>
  </table>
  <? endif; ?>
</div>
<? endif ?>
