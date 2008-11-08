<? defined("SYSPATH") or die("No direct script access."); ?>
<table class="gMetadata">
  <tbody>
    <tr>
      <th>Title:</th>
      <td><?= $item->title; ?></td>
    </tr>
    <tr>
      <th>Description:</th>
      <td><?= $item->description; ?></td>
    </tr>
    <tr>
      <th>Name:</th>
      <td><?= $item->name; ?></td>
    </tr>
    <tr>
      <th>Owner:</th>
      <td><a href="#"><?= isset($item->user_id) ? $item->user->name : "anonymous"?></a></td>
    </tr>
    <tr>
      <td colspan="2" class="toggle">
        <a href="#">more \/</a>
      </td>
    </tr>
  </tbody>
</table>


