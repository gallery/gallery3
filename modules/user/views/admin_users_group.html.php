<?php defined("SYSPATH") or die("No direct script access.") ?>
<h4>
  <a href="<?php echo  url::site("admin/users/edit_group_form/$group->id") ?>"
   title="<?php echo  t("Edit the %name group's name", array("name" => $group->name))->for_html_attr() ?>"
   class="g-dialog-link"><?php echo  html::clean($group->name) ?></a>
  <?php if (!$group->special): ?>
  <a href="<?php echo  url::site("admin/users/delete_group_form/$group->id") ?>"
    title="<?php echo  t("Delete the %name group", array("name" => $group->name))->for_html_attr() ?>"
    class="g-dialog-link g-button g-right">
    <span class="ui-icon ui-icon-trash"><?php echo  t("Delete") ?></span></a>
  <?php else: ?>
  <a title="<?php echo  t("This default group cannot be deleted")->for_html_attr() ?>"
     class="g-button g-right ui-state-disabled ui-icon-left">
    <span class="ui-icon ui-icon-trash"><?php echo  t("Delete") ?></span></a>
  <?php endif ?>
</h4>

<?php if ($group->users->count_all() > 0): ?>
<ul class="g-member-list">
  <?php foreach ($group->users->order_by("name", "ASC")->find_all() as $i => $user): ?>
  <li class="g-user">
    <?php echo  html::clean($user->name) ?>
    <?php if (!$group->special): ?>
    <a href="javascript:remove_user(<?php echo  $user->id ?>, <?php echo  $group->id ?>)"
       class="g-button g-right ui-state-default ui-corner-all ui-icon-left"
       title="<?php echo  t("Remove %user from %group group",
              array("user" => $user->name, "group" => $group->name))->for_html_attr() ?>">
      <span class="ui-icon ui-icon-closethick"><?php echo  t("Remove") ?></span>
    </a>
    <?php endif ?>
  </li>
  <?php endforeach ?>
</ul>
<?php else: ?>
<div>
  <p class="ui-state-disabled">
    <?php echo  t("Drag &amp; drop users from the \"Users\" list onto this group to add group members.") ?>
  </p>
</div>
<?php endif ?>
