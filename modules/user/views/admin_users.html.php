<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var add_user_to_group_url = "<?= url::site("admin/users/add_user_to_group/__USERID__/__GROUPID__?csrf=" . access::csrf_token()) ?>";
  $(document).ready(function(){
    $("#gUserAdminList .core-info").draggable({
      helper: "clone"
    });
    $("#gGroupAdmin .gGroup").droppable({
      accept: ".core-info",
      hoverClass: "gSelected",
      drop: function(ev, ui) {
        var user_id = $(ui.draggable).attr("id").replace("user-", "");
        var group_id = $(this).attr("id").replace("group-", "");
        $.get(add_user_to_group_url.replace("__USERID__", user_id).replace("__GROUPID__", group_id),
              {},
              function() {
                reload_group(group_id);
              });
      }
    });
    $("#group-1").droppable("destroy");
    $("#group-2").droppable("destroy");
  });

  var reload_group = function(group_id) {
    var reload_group_url = "<?= url::site("admin/users/group/__GROUPID__") ?>";
    $.get(reload_group_url.replace("__GROUPID__", group_id),
          {},
          function(data) {
            $("#group-" + group_id).html(data);
          });
  }

  var remove_user = function(user_id, group_id) {
    var remove_user_url = "<?= url::site("admin/users/remove_user_from_group/__USERID__/__GROUPID__?csrf=" . access::csrf_token()) ?>";
    $.get(remove_user_url.replace("__USERID__", user_id).replace("__GROUPID__", group_id),
          {},
          function() {
            reload_group(group_id);
          });
  }
</script>
<div class="gBlock">
  <a href="<?= url::site("admin/users/add_user_form") ?>"
     class="gDialogLink gButtonLink right"
     title="<?= t("Create a new user") ?>">
    + <?= t("Add a new user") ?>
  </a>

  <h2>
    <?= t("User Admin") ?>
  </h2>

  <div class="gBlockContent">
    <ul id="gUserAdminList">
      <li class="gHeaderRow">
    	<strong><?= t("Username") ?></strong> <?= t("(Full name)") ?>
	<span class="understate"><?= t("last login") ?></span>
      </li>

      <? foreach ($users as $i => $user): ?>
      <li class="<?= text::alternate("gOddRow", "gEvenRow") ?> user">
        <div id="user-<?= $user->id ?>" class="core-info" style="display: inline">
          <img src="<?= $user->avatar_url(20, $theme->url("images/avatar.jpg", true)) ?>"
               title="<?= t("Drag user onto group below to add as a new member") ?>"
               alt="<?= $user->name ?>"
               width="20"
               height="20" />
          <strong><?= $user->name ?></strong>
        </div>
        (<?= $user->full_name ?>)
        <span class="understate">
          <?= ($user->last_login == 0) ? "" : date("m j, y", $user->last_login) ?>
        </span>
        <span class="gActions">
          <a href="users/edit_form/<?= $user->id ?>" class="gPanelLink"><?= t("edit") ?></a>
          <? if (user::active()->id != $user->id && !$user->guest): ?>
          <a href="users/delete_form/<?= $user->id ?>" class="gDialogLink"><?= t("delete") ?></a>
          <? else: ?>
          <span class="inactive" title="<?= t("This user cannot be deleted") ?>">
            <?= t("delete") ?>
          </span>
          <? endif ?>
        </span>
      </li>
      <? endforeach ?>
    </ul>
    <p>
      <a href="<?= url::site("admin/users/add_user_form") ?>"
         class="gDialogLink gButtonLink"
         title="<?= t("Create a new user") ?>">
        + <?= t("Add a new user") ?>
      </a>
    </p>
  </div>
</div>

<div id="gGroupAdmin" class="gBlock">
  <a href="groups/add_form"
     class="gDialogLink gButtonLink right"
     title="<?= t("Create a new group") ?>">
    + <?= t("Add a new group") ?>
  </a>

  <h2>
    <?= t("Group Admin") ?>
  </h2>

  <div class="gBlockContent">
    <ul>
      <? foreach ($groups as $i => $group): ?>
      <li id="group-<?= $group->id ?>" class="gGroup">
        <? $v = new View("admin_users_group.html"); $v->group = $group; ?>
        <?= $v ?>
      </li>
      <? endforeach ?>
    </ul>
  </div>
</div>
