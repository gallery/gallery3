<? defined("SYSPATH") or die("No direct script access."); ?>
<?= $theme->header_top() ?>
<img id="gLogo" alt="<?= _("Logo") ?>" src="<?= $theme->url("images/logo.png") ?>" />

<div id="gSiteMenu" class="gClearFix">
  <ul class="ui-tabs-nav">
    <li><a href="<?= url::base() ?>"><?= _("HOME") ?></a></li>
    <li><a class="active" href="<?= url::site("albums/1") ?>"><?= _("BROWSE") ?></a></li>
    <li><a href="#"><?= _("UPLOAD") ?></a></li>
    <li><a href="#"><?= _("MY GALLERY") ?></a></li>
    <li><a href="#"><?= _("ADMIN") ?></a></li>
  </ul>
</div>

<?= $theme->header_bottom() ?>

<? if ($page_type != "tag"): ?>
<ul id="gBreadcrumbs" class="gClearFix">
  <? foreach ($parents as $parent): ?>
  <li><a href="<?= url::site("albums/{$parent->id}") ?>"><?= $parent->title_edit ?></a></li>
  <? endforeach ?>
  <li class="active"><?= $item->title_edit ?></li>
</ul>
<? endif ?>
