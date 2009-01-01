<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul id="gLoginMenu">
  <? if ($user->guest): ?>
  <li><a href="<?= url::site("login") ?>"
         title="<?= _("Login to Gallery") ?>"
         id="gLoginLink"><?= _("Login") ?></a></li>
  <? else: ?>
  <li><a href="<?= url::site("form/edit/users/{$user->id}") ?>"
      title="<?= _("Edit Your Profile") ?>"
      id="gUserProfileLink" class="gDialogLink"><?= _("Modify Profile") ?></a></li>
  <li><a href="<?= url::site("logout?continue=" . url::current(true)) ?>"
         id="gLogoutLink"><?= _("Logout") ?></a></li>
  <? endif; ?>
</ul>
