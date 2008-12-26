<? defined("SYSPATH") or die("No direct script access."); ?>
<div class="gBlock">
  <h2>User Administration</h2>
  <div class="gBlockContent">
    <p>These are the users in your system</p>
    <ul>
      <? foreach ($users as $i => $user): ?>
      <li>
        <?= $user->name ?>
        <?= ($user->last_login == 0) ? "" : "(" . date("M j, Y", $user->last_login) . ")" ?>
        <a href="users/edit/<?= $user->id ?>" class="gDialogLink">edit</a>
        <? if (!(user::active()->id == $user->id || user::guest()->id == $user->id)): ?>
        <a href="users/delete/<?= $user->id ?>" class="gDialogLink">delete</a>
        <? endif ?>
      </li>
      <? endforeach ?>
      <li><a href="users/create" class="gDialogLink">Add user</a></li>
    </ul>
  </div>
</div>
