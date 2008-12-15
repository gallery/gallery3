<? defined("SYSPATH") or die("No direct script access."); ?>
<ul id="gLoginMenu">
  <? if ($user->guest): ?>
    <li><a href="<?= url::site("login") ?>" id="gLoginLink"><?= _("Login") ?></a></li>
  <? else: ?>
    <li><a href="<?= url::site("user/{$user->id}?continue=" . url::current(true))?>"
        id="gUserProfileLink"><?= _("Modify Profile") ?></a></li>
    <li><a href="<?= url::site("logout?continue=" . url::current(true)) ?>" 
        id="gLogoutLink"><?= _("Logout") ?></a></li>
  <? endif; ?>
</ul>
