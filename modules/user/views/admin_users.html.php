<?php defined("SYSPATH") or die("No direct script access.") ?>
<style>
	.gButtonLink {
		border-width: 1px;
		border-style: solid;
		border-color: #ececec #c8c8c8 #c8c8c8 #ececec;
		background-image: url('/gallery3/themes/admin_default/images/backg-buttonlink.png');
		padding: .2em .3em;
		font-weight: bold;
	}
	.gButtonLink:hover {
		border-color: #c8c8c8 #ececec #ececec #c8c8c8;
	}
	.gBlock h2 a {
		font-size: .7em;
		float: right;
		position: relative;
		top: -1.69em;
	}
	
	.gUserAdminList li {
		padding: .4em .4em .3em .4em;
		position: relative;
	}
	.gUserAdminList li img {
		width: 20px;
		height: 20px;
		cursor: move;
	}
	.gFirstRow {
		border-bottom: 1px solid grey;
		padding-bottom: .5em;
		padding-left: 30px !important;
	}
	.gOddRow {
		background-color: #f1f1f1;
	}
	.gActions {
		position: absolute;
		left: 400px;
	}
	.gActions a, .gActions span {
		margin-right: 40px;
	}
	.gPanel {
    display: none;
    padding: 1em;
	}
  .gPanel legend {
    display: none;
  }
  .gPanel fieldset {
    border: none;
  }
	
	li.gGroup {
		float: left;
		display: block;
		width: 200px;
		height: 200px;
		border: 1px solid gray;
		padding: .5em;
		margin-right: 1em;
	}
</style>

<div class="gBlock">
  <h2>
  	<?= t("User Admin") ?>
		<a class="gButtonLink" href="#" title="<?= t("Create a new user") ?>">+ <?= t("Add user") ?></a>
	</h2>
	
  <div class="gBlockContent">
    <ul class="gUserAdminList">
    	<li class="gFirstRow">
    		<strong>Username</strong> (Full name)
				<span class="understate">last login</span>
    	</li>
			
      <? foreach ($users as $i => $user): ?>
      <li class="<?= ($i % 2 == 0) ? "gEvenRow" : "gOddRow" ?>">
        <img src="<?= $theme->url("images/avatar.jpg") ?>"
          title="<?= t("Drag user onto group below to add as a new member") ?>"
          width="20" height="20" />
        <strong><?= $user->name ?></strong>
        (<?= $user->full_name ?>)
        <span class="understate">
          <?= ($user->last_login == 0) ? "" : date("M j, Y", $user->last_login) ?>
        </span>
        <span class="gActions">
          <a href="users/edit_form/<?= $user->id ?>" class="gPanelLink"><?= t("edit") ?></a>
          <? if (!(user::active()->id == $user->id || user::guest()->id == $user->id)): ?>
            <a href="users/delete_form/<?= $user->id ?>" class="gDialogLink"><?= t("delete") ?></a>
          <? else: ?>
            <span class="inactive" title="<?= t("This user can't be deleted") ?>">
              <?= t("delete") ?>
            </span>
          <? endif ?>
        </span>
      </li>
      <? endforeach ?>
    </ul>
		<br />
		<a href="users/add_form" class="gDialogLink gButtonLink" title="<?= t("Create a new user") ?>">
  		+ <?= t("Add a new user") ?>
		</a>
  </div>
</div>

<div class="gBlock">
  <h2>
  	<?= t("Group Admin") ?>
		<a class="gButtonLink" href="#" title="<?= t("Create a new group") ?>">+ <?= t("Add group") ?></a>
	</h2>
	
  <div id="gGroupAdmin" class="gBlockContent">
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
			
			<li class="gGroup">
				<a href="groups/add_form" title="<?= t("Create a new group") ?>">
  				+ <?= t("Add a new group") ?>
				</a>
			</li>
		</ul>
  </div>
</div>
