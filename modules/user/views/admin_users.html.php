<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="gBlock">
	<a href="users/add_form" class="gDialogLink gButtonLink right"
      title="<?= t("Create a new user") ?>">
  	+ <?= t("Add a new user") ?>
	</a>

  <h2>
  	<?= t("User Admin") ?>
	</h2>
	
  <div class="gBlockContent">
    <ul class="gUserAdminList">
      <li class="gFirstRow">
    	<strong><?= t("Username") ?></strong> <?= t("(Full name)") ?>
	<span class="understate"><?= t("last login") ?></span>
      </li>

      <? foreach ($users as $i => $user): ?>
      <li class="<?= text::alternate("gOddRow", "gEvenRow") ?>">
        <img src="<?= $theme->url("images/avatar.jpg") ?>"
          title="<?= t("Drag user onto group below to add as a new member") ?>"
          alt="<?= $user->name ?>" 
          width="20"
          height="20" />
        <strong><?= $user->name ?></strong>
        (<?= $user->full_name ?>)
        <span class="understate">
          <?= ($user->last_login == 0) ? "" : date("m j, y", $user->last_login) ?>
        </span>
        <span class="gactions">
          <a href="users/edit_form/<?= $user->id ?>" class="gPanelLink"><?= t("edit") ?></a>
          <? if (user::active()->id != $user->id && !$user->guest): ?>
          <a href="users/delete_form/<?= $user->id ?>" class="gDialogLink"><?= t("delete") ?></a>
          <? else: ?>
          <span class="inactive" title="<?= t("This user can't be deleted") ?>">
            <?= t("delete") ?>
          </span>
        <? endif ?>
      </li>
      <? endforeach ?>
    </ul>
    <br />
    <a href="<?= url::site("admin/users/add_form") ?>" class="gDialogLink gButtonLink"
       title="<?= t("Create a new user") ?>">
      + <?= t("Add a new user") ?>
    </a>
  </div>
</div>

<div id="gGroupAdmin" class="gBlock">
  <a href="groups/add_form" title="<?= t("Create a new group") ?>" class="gDialogLink gButtonLink right">
  	+ <?= t("Add a new group") ?>
	</a>

  <h2>
  	<?= t("Group Admin") ?>
	</h2>

  <div class="gBlockContent">
  	<ul>
  		<? foreach ($groups as $i => $group): ?>
			<li class="gGroup">
				<strong><?= $group->name?></strong><br />
				<ul>
					<? foreach ($group->users as $i => $user): ?>
					<li class="gUser">
						<?= $user->name ?>
						<a href="groups/remove_users/<?= $user->id ?>">X</a>
					</li>
					<? endforeach ?>
				</ul>
			</li>
			<? endforeach ?>
		</ul>
  </div>
</div>
