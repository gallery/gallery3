<?php defined("SYSPATH") or die("No direct script access.") ?>
<table>
  <?php foreach ($user_profile_data as $label => $value): ?>
  <tr>
    <th><?php echo html::clean($label) ?></th>
    <td><?php echo html::purify($value) ?></td>
  </tr>
  <?php endforeach ?>
</table>
