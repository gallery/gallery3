<? defined("SYSPATH") or die("No direct script access."); ?>
<img id="gLogo" alt="<?= _("Logo") ?>" src="<?= $theme->url("images/logo.png") ?>" />
<h1><?= $item->title_edit ?></h1>

<div id="gLoginMenu">
  <? if ($user): ?>
    <a href="<?= url::site("user/update")?>"><?= _("Modify Profile") ?></a> |
    <a href="<?= url::site("logout")?>" id="gLogoutLink"><?= _("Logout") ?></a>
  <? else: ?>
    <a href="#"><?=_("Recover password") ?></a> |
    <a href="<?= url::site("user/register")?>"><?= _("Register") ?></a> |
    <span id="gLoginLink">
      <a href="javascript:show_login()"><?= _("Login") ?></a>
    </span>
    <span id="gLoginClose" class="gDisplayNone">
      <?= _("Login") ?> | <a href="javascript:close_login()">X</a>
    </span>
  <? endif; ?>
  <span id="gLoginForm" class="gDisplayNone" formSrc="<?= url::site("login") ?>"></span>
</div>
<ul id="gSiteMenu">
  <li><a href="<?= url::base() ?>"><?= _("HOME") ?></a></li>
  <li><a class="active" href="<?= url::site("album/1") ?>"><?= _("BROWSE") ?></a></li>
  <li><a href="#"><?= _("UPLOAD") ?></a></li>
  <li><a href="#"><?= _("MY GALLERY") ?></a></li>
  <li><a href="#"><?= _("ADMIN") ?></a></li>
</ul>

<form id="gSearchForm" class="gInline">
  <ul class="gNoLabels">
    <li><input type="text" value="<?= _("Search Gallery ...") ?>"/></li>
    <li><input type="submit" value="search" /></li>
  </ul>
</form>

<ul id="gBreadcrumbs">
  <? foreach ($parents as $parent): ?>
  <li><a href="<?= url::site("album/{$parent->id}") ?>"><?= $parent->title_edit ?></a></li>
  <? endforeach ?>
  <li class="active"><?= $item->title_edit ?></li>
</ul>
