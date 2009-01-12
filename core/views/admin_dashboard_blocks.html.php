<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gAdminDashboardBlocks">
  <table border="1">
    <tr>
      <th> <?= t("Main") ?> </th>
      <th> <?= t("Sidebar") ?> </th>
    </tr>

    <? foreach ($available as $module_name => $block_list): ?>
    <? foreach ($block_list as $id => $title): ?>
    <tr>
      <td>
        <?= $title ?>
      </td>
      <td>
        <option>
        </option>
      </td>
    </tr>
    <? endforeach ?>
    <? endforeach ?>
  </table>
</div>
<? printf("<pre>%s</pre>",print_r($available,1));flush(); ?>
<? printf("<pre>%s</pre>",print_r($displayed,1));flush(); ?>
