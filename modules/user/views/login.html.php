<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul id="gLoginMenu">
  <? if ($user->guest): ?>
  <li class="first"><a href="<?= url::site("login/ajax") ?>"
      title="<?= t("Login to Gallery") ?>"
      id="gLoginLink"><?= t("Login") ?></a></li>
  <? else: ?>
     <li class="first"><?=
        t('Logged in as %name',
          array('name' =>
                '<a href="' . url::site("form/edit/users/{$user->id}") .
                '" title="' . t("Edit Your Profile") .
                '" id="gUserProfileLink" class="gDialogLink">' .
                $user->full_name . '</a>')) ?></li>
  <li><a href="<?= url::site("logout?continue=" . url::current(true)) ?>"
      id="gLogoutLink"><?= t("Logout") ?></a></li>
  <? endif; ?>
</ul>
