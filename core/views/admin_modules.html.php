<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gModules">
  <table>
    <tr>
      <th> <?= _("Installed") ?> </th>
      <th> <?= _("Name") ?> </th>
      <th> <?= _("Version") ?> </th>
      <th> <?= _("Description") ?> </th>
    </tr>
    <? foreach ($available as $module_name => $module_info):  ?>
    <tr>
      <td> <?= form::checkbox($module_name, '', module::is_installed($module_name)) ?> </td>
      <td> <?= _($module_info["name"]) ?> </td>
      <td> <?= module::get_version($module_name) ?> </td>
      <td> <?= _($module_info["description"]) ?> </td>
    </tr>
    <? endforeach ?>
  </table>
</div>
