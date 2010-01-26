<?php defined("SYSPATH") or die("No direct script access.") ?>
<table>
  <? foreach ($fields as $field => $value): ?>
  <tr>
    <td><?= $field ?></td>
    <td><?= $value ?></td>
  </tr>
  <? endforeach ?>
</table>
