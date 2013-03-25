<?php defined("SYSPATH") or die("No direct script access.") ?>
<table>
  <? foreach ($user_profile_data as $label => $value): ?>
  <tr>
    <th><?= HTML::clean($label) ?></th>
    <td><?= HTML::purify($value) ?></td>
  </tr>
  <? endforeach ?>
</table>
