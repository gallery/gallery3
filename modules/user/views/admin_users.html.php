<? defined("SYSPATH") or die("No direct script access."); ?>
<div class="gBlock">
  <a href="" class="gClose">X</a>
  <h2>User Administration</h2>
  <div class="gBlockContent">
    <p>These are the users in your system</p>
    <ul class="ui-accordion-container" id="gEditUserContainer">
      <? foreach ($users as $i => $user): ?>
        <li id="<?= 'accordion' . $user->id ?>">
            <?= $user->name ?>
            <?= ($user->last_login == 0) ? "" :
            "(" . date("M j, Y", $user->last_login) . ")" ?> <br />
          <a href="#">edit</a>
          <div>
          <?
            $form = user::get_edit_form($user,
              "users/{$user->id}?_method=put&continue=/admin/users");
            $form->set_attr("id", "gEdit" . $user->id);
            print $form;
          ?>
          </div>
          <br />
          <?= (user::active()->id == $user->id) ? "&nbsp;" :
          "<a href=\"" . url::site("admin/users/delete/$user->id") . "\">delete</a>" ?>
          <br /><br />
        </li>
      <? endforeach ?>
      <li><a href="#">Add user</a>
          <div>
          <?
            $form = user::get_add_form($user,
              "users/add?_method=post&continue=/admin/users");
            $form->set_attr("id", "gEdit" . $user->id);
            print $form;
          ?>
          </div>
      </li>
    </ul>
  </div>
  <h2>Group Administration</h2>
  <div class="gBlockContent">
    <p>These are the groups in your system</p>
  </div>
</div>
