<?php defined("SYSPATH") or die("No direct script access.") ?>
<table>
  <? foreach ($user_profile_data as $label => $value): ?>
  <tr>
    <td><?= html::clean($label) ?></td>
    <td><?= html::purify($value) ?></td>
  </tr>
  <? endforeach ?>
</table>
