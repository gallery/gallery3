<? defined("SYSPATH") or die("No direct script access."); ?>
<img id="gLogo" alt="<?= _("Logo") ?>" src="<?= $theme->url("images/logo.png") ?>" />
<h1><?= $item->title_edit ?></h1>

<div id="gLoginMenu">
  <? if (!user::is_logged_in($user)): ?>
    <a href="<?= url::site("user/register")?>"><?= _("Register") ?></a> | 
    <a href="<?= url::site("login")?>"><?= _("Login") ?></a>
  <? else: ?>
    <a href="<?= url::site("user/update")?>"><?= _("Modify Profile") ?></a> | 
    <a href="<?= url::site("logout")?>"><?= _("Logout") ?></a>
  <? endif; ?>
</div>
<ul id="gSiteMenu">
  <li><a href="<?= url::base() ?>"><?= _("HOME") ?></a></li>
  <li><a class="active" href="<?= url::site("album/1") ?>"><?= _("BROWSE") ?></a></li>
  <li><a href="#"><?= _("UPLOAD") ?></a></li>
  <li><a href="#"><?= _("MY GALLERY") ?></a></li>
  <li><a href="#"><?= _("ADMIN") ?></a></li>
</ul>

<form id="gSearchForm">
  <input type="text" class="text" value="<?= _("Search Gallery ...") ?>"/>
  <input type="submit" class="submit" value="search" />
</form>

<ul id="gBreadcrumbs">
  <? foreach ($parents as $parent): ?>
  <li><a href="<?= url::site("album/{$parent->id}") ?>"><?= $parent->title_edit ?></a></li>
  <? endforeach ?>
  <li class="active"><?= $item->title_edit ?></li>
</ul>
