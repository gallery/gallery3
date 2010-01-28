<?php defined("SYSPATH") or die("No direct script access.") ?>
<table>
  <? foreach ($fields as $field => $value): ?>
  <tr>
    <td><?= $field ?></td>
    <td><?= html::purify($value) ?></td>
  </tr>
  <? endforeach ?>
</table>
