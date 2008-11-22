<? defined("SYSPATH") or die("No direct script access."); ?>
<img id="gLogo" alt="<?= _("Logo") ?>" src="<?= $theme->url("images/logo.png") ?>" />

<?= View::top($theme) ?>

<div id="gSiteMenu" class="gClearFix">
  <ul class="ui-tabs-nav">
    <li><a href="<?= url::base() ?>"><?= _("HOME") ?></a></li>
    <li><a class="active" href="<?= url::site("albums/1") ?>"><?= _("BROWSE") ?></a></li>
    <li><a href="#"><?= _("UPLOAD") ?></a></li>
    <li><a href="#"><?= _("MY GALLERY") ?></a></li>
    <li><a href="#"><?= _("ADMIN") ?></a></li>
  </ul>
</div>

<form id="gSearchForm" class="gInline">
  <ul class="gNoLabels">
    <li><label for="search">Search</label><input type="text" name="search" value="<?= _("Search Gallery ...") ?>"/></li>
    <li><input type="submit" value="<?= _("search") ?>" /></li>
  </ul>
</form>

<ul id="gBreadcrumbs" class="gClearFix">
  <? foreach ($parents as $parent): ?>
  <li><a href="<?= url::site("albums/{$parent->id}") ?>"><?= $parent->title_edit ?></a></li>
  <? endforeach ?>
  <li class="active"><?= $item->title_edit ?></li>
</ul>
