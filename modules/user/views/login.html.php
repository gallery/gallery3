<? defined("SYSPATH") or die("No direct script access."); ?>
<ul id="gLoginMenu">
  <? if ($user): ?>
    <a href="<?= url::site("user/{$user->id}?continue=" . url::current(true))?>"><?= _("Modify Profile") ?></a>
  | <a href="<?= url::site("logout?continue=" . url::current(true)) ?>" id="gLogoutLink">
      <?= _("Logout") ?>
    </a>
  <? else: ?>
    <span id="gLoginLink">
    <a href="javascript:show_login('<?= url::site("login") ?>')">
      <?= _("Login") ?>
    </a>
  </span>
  <span id="gLoginClose" class="gDisplayNone">
    <?= _("Login") ?> | <a href="javascript:close_login()">X</a>
  </span>
  <div id="gLoginFormContainer"></div>
  <? endif; ?>
</ul>

