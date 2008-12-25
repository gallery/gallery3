<? defined("SYSPATH") or die("No direct script access."); ?>
<div class="gBlock">
  <h2>User Administration</h2>
  <div class="gBlockContent">
    <p>These are the users in your system</p>
    <ul class="ui-accordion-container" id="gUsers">
      <? foreach ($users as $i => $user): ?>
      <li>
        <?= $user->name ?>
        <?= ($user->last_login == 0) ? "" : "(" . date("M j, Y", $user->last_login) . ")" ?>
        <a href="#">edit</a>
        <div>
          <?= user::get_edit_form_admin($user); ?>
        </div>
        <? if (!(user::active()->id == $user->id || user::guest()->id == $user->id)): ?>
        <a href="#">delete</a>
        <div>
          <?= user::get_delete_form_admin($user, "admin/users/delete/{$user->id}"); ?>
        </div>
        <? endif ?>
      </li>
      <? endforeach ?>
      <li><a href="#">Add user</a>
        <div id="gAddUser">
          <?= user::get_add_form_admin(); ?>
        </div>
      </li>
    </ul>
  </div>
  <h2>Group Administration</h2>
  <div class="gBlockContent">
    <p>These are the groups in your system</p>
  </div>
  <ul class="ui-accordion-container">
    <? foreach ($groups as $i => $group): ?>
    <li>
      <?= $group->name ?>
      <a href="#">edit</a>
      <div>
        <?= group::get_edit_form($group, "groups/{$group->id}?_method=put"); ?>
      </div>
      <? if (!$group->special): ?>
      <a href="#">delete</a>
      <div>
        <?= group::get_delete_form($group,
            "groups/{$group->id}?_method=delete"); ?>
      </div>
      <? endif ?>
    </li>
    <? endforeach ?>
    <li><a href="#">Add group</a>
      <div>
        <?= group::get_add_form("groups/add?_method=post"); ?>
      </div>
    </li>
  </ul>
</div>
