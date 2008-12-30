<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gModules">
  <h1> <?= _("Gallery Modules") ?> </h1>
  <p>
    <?= _("Power up your Gallery by adding more modules!   Each module provides new cool features.") ?>
  </p>

  <form method="post" action="<?= url::site("admin/modules/save") ?>">
    <?= access::csrf_form_field() ?>
    <table>
      <tr>
        <th> <?= _("Installed") ?> </th>
        <th> <?= _("Name") ?> </th>
        <th> <?= _("Version") ?> </th>
        <th> <?= _("Description") ?> </th>
      </tr>
      <? foreach ($available as $module_name => $module_info):  ?>
      <tr>
        <? $data = array("name" => $module_name); ?>
        <? if ($module_info->locked) $data["disabled"] = 1; ?>
        <td> <?= form::checkbox($data, '1', module::is_installed($module_name)) ?> </td>
        <td> <?= _($module_info->name) ?> </td>
        <td> <?= $module_info->version ?> </td>
        <td> <?= _($module_info->description) ?> </td>
      </tr>
      <? endforeach ?>
    </table>
    <input type="submit" value="<?= _("Update") ?>"/>
  </form>
</div>
