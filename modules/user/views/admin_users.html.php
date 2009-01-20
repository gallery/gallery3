<?php defined("SYSPATH") or die("No direct script access.") ?>
<style>
	.gButtonLink {
		border-width: 1px;
		border-style: solid;
		border-color: #ececec #c8c8c8 #c8c8c8 #ececec;
		background-image: url('/gallery3/themes/admin_default/images/backg-buttonlink.png');
		padding: .2em .3em .2em .3em;
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
		width: 20px
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
	.gActions a {
		margin-right: 40px;
	}
	.gUserEdit {
		display: none;
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
    		<strong>Username</strong>
				(Full name)
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
						<a href="#" onclick="$('gUserEdit-<?= $user->id ?>').slideDown('slow');"><?= t("edit") ?></a>
		        <!--<a href="users/edit_form/<?= $user->id ?>" class="gDialogLink"><?= t("edit") ?></a>-->
		        <? if (!(user::active()->id == $user->id || user::guest()->id == $user->id)): ?>
		        	<a href="users/delete_form/<?= $user->id ?>" class="gDialogLink"><?= t("delete") ?></a>
		        <? endif ?>
					</span>
	      </li>
				
				<li id="gUserEdit-<?= $user->id ?>" class="gUserEdit">
					<form>
						<fieldset>
							<label>Username</label>
							<input type="text" />
							<label>Full name</label>
							<input type="text" />
							<label>Email</label>
							<input type="text" />
							...
							<input type="submit" value="Save changes" />
							<a href="#">cancel</a>
						</fieldset>
					</form>
				</li>
      <? endforeach ?>
    </ul>
		<br />
		<a href="users/add_form" class="gDialogLink gButtonLink" title="<?= t("Create a new user") ?>">
  		+ <?= t("Add a new user") ?>
		</a>
  </div>
</div>
