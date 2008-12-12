<? defined("SYSPATH") or die("No direct script access."); ?>
<ul id="gLoginMenu">
  <? if ($user->guest): ?>
    <li id="gLoginFormContainer"></li>
    <li id="gLoginLink"><a href="<?= url::site("login") ?>">Login</a></li>
  <? else: ?>
    <li><a href="<?= url::site("user/{$user->id}?continue=" . url::current(true))?>">
      <?= _("Modify Profile") ?></a></li>
    <li><a href="<?= url::site("logout?continue=" . url::current(true)) ?>" id="gLogoutLink">
      <?= _("Logout") ?></a></li>
  <? endif; ?>
</ul>
