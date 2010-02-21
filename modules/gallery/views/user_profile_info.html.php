<?php defined("SYSPATH") or die("No direct script access.") ?>
<table>
  <? foreach ($user_profile_data as $label => $value): ?>
  <tr>
    <th><?= html::clean($label) ?></th>
    <td><?= html::purify($value) ?></td>
  </tr>
  <? endforeach ?>
</table>
