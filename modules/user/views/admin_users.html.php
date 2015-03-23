<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var add_user_to_group_url = "<?php echo url::site("admin/users/add_user_to_group/__USERID__/__GROUPID__?csrf=$csrf") ?>";
  $(document).ready(function(){
    $("#g-user-admin-list .g-core-info").draggable({
      helper: "clone"
    });
    $("#g-group-admin .g-group").droppable({
      accept: ".g-core-info",
      hoverClass: "g-selected",
      drop: function(ev, ui) {
        var user_id = $(ui.draggable).attr("id").replace("g-user-", "");
        var group_id = $(this).attr("id").replace("g-group-", "");
        $.get(add_user_to_group_url.replace("__USERID__", user_id).replace("__GROUPID__", group_id),
              {},
              function() {
                reload_group(group_id);
              });
      }
    });
    $("#g-group-1").droppable("destroy");
    $("#g-group-2").droppable("destroy");
  });

  var reload_group = function(group_id) {
    var reload_group_url = "<?php echo url::site("admin/users/group/__GROUPID__") ?>";
    $.get(reload_group_url.replace("__GROUPID__", group_id),
          {},
          function(data) {
            $("#g-group-" + group_id).html(data);
            $("#g-group-" + group_id + " .g-dialog-link").gallery_dialog();
          });
  }

  var remove_user = function(user_id, group_id) {
    var remove_user_url = "<?php echo url::site("admin/users/remove_user_from_group/__USERID__/__GROUPID__?csrf=$csrf") ?>";
    $.get(remove_user_url.replace("__USERID__", user_id).replace("__GROUPID__", group_id),
          {},
          function() {
            reload_group(group_id);
          });
  }
</script>

<div class="g-block">
  <h1> <?php echo t("Users and groups") ?> </h1>

  <div class="g-block-content">

    <div id="g-user-admin" class="g-block">
      <a href="<?php echo url::site("admin/users/add_user_form") ?>"
          class="g-dialog-link g-button g-right ui-icon-left ui-state-default ui-corner-all"
          title="<?php echo t("Create a new user")->for_html_attr() ?>">
        <span class="ui-icon ui-icon-circle-plus"></span>
        <?php echo t("Add a new user") ?>
      </a>

      <h2> <?php echo t("Users") ?> </h2>

      <div class="g-block-content">
        <table id="g-user-admin-list">
          <tr>
            <th><?php echo t("Username") ?></th>
            <th><?php echo t("Full name") ?></th>
            <th><?php echo t("Email") ?></th>
            <th><?php echo t("Last login") ?></th>
            <th><?php echo t("Albums/Photos") ?></th>
            <th><?php echo t("Actions") ?></th>
          </tr>

          <?php foreach ($users as $i => $user): ?>
          <tr id="g-user-<?php echo $user->id ?>" class="<?php echo text::alternate("g-odd", "g-even") ?> g-user <?php echo $user->admin ? "g-admin" : "" ?>">
            <td id="g-user-<?php echo $user->id ?>" class="g-core-info g-draggable">
              <img src="<?php echo $user->avatar_url(20, $theme->url("images/avatar.jpg", true)) ?>"
                   title="<?php echo t("Drag user onto a group to add as a new member")->for_html_attr() ?>"
                   alt="<?php echo html::clean_attribute($user->name) ?>"
                   width="20"
                   height="20" />
              <?php echo html::clean($user->name) ?>
            </td>
            <td>
              <?php echo html::clean($user->full_name) ?>
            </td>
            <td>
              <?php echo html::clean($user->email) ?>
            </td>
            <td>
              <?php echo ($user->last_login == 0) ? "" : gallery::date($user->last_login) ?>
            </td>
            <td>
              <?php echo db::build()->from("items")->where("owner_id", "=", $user->id)->count_records() ?>
            </td>
            <td>
              <a href="<?php echo url::site("admin/users/edit_user_form/$user->id") ?>"
                  data-open-text="<?php echo t("Close")->for_html_attr() ?>"
                  class="g-panel-link g-button ui-state-default ui-corner-all ui-icon-left">
                <span class="ui-icon ui-icon-pencil"></span><span class="g-button-text"><?php echo t("Edit") ?></span></a>
              <?php if (identity::active_user()->id != $user->id && !$user->guest): ?>
              <a href="<?php echo url::site("admin/users/delete_user_form/$user->id") ?>"
                  class="g-dialog-link g-button ui-state-default ui-corner-all ui-icon-left">
                <span class="ui-icon ui-icon-trash"></span><?php echo t("Delete") ?></a>
              <?php else: ?>
              <span title="<?php echo t("This user cannot be deleted")->for_html_attr() ?>"
                  class="g-button ui-state-disabled ui-corner-all ui-icon-left">
                <span class="ui-icon ui-icon-trash"></span><?php echo t("Delete") ?></span>
              <?php endif ?>
            </td>
          </tr>
          <?php endforeach ?>
        </table>

        <div class="g-paginator">
          <?php echo $theme->paginator() ?>
        </div>

      </div>
    </div>

    <div id="g-group-admin" class="g-block ui-helper-clearfix">
      <a href="<?php echo url::site("admin/users/add_group_form") ?>"
          class="g-dialog-link g-button g-right ui-icon-left ui-state-default ui-corner-all"
          title="<?php echo t("Create a new group")->for_html_attr() ?>">
        <span class="ui-icon ui-icon-circle-plus"></span>
        <?php echo t("Add a new group") ?>
      </a>

      <h2> <?php echo t("Groups") ?> </h2>

      <div class="g-block-content">
        <ul>
          <?php foreach ($groups as $i => $group): ?>
          <li id="g-group-<?php echo $group->id ?>" class="g-group g-left <?php echo ($group->special ? "g-default-group" : "") ?>">
            <?php $v = new View("admin_users_group.html"); $v->group = $group; ?>
            <?php echo $v ?>
          </li>
          <?php endforeach ?>
        </ul>
      </div>
    </div>

  </div>
</div>
