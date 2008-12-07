<? defined("SYSPATH") or die("No direct script access."); ?>
<?= $theme->header_top() ?>
<img id="gLogo" alt="<?= _("Logo") ?>" src="<?= $theme->url("images/logo.png") ?>" />

<div id="gSiteMenu" class="gClearFix">
  <span class="ui-tabs-nav">
  <?= $theme->site_navigation() ?>
  </span>
</div>

<?= $theme->header_bottom() ?>

<? if ($page_type != "tag"): ?>
<ul id="gBreadcrumbs" class="gClearFix">
  <? foreach ($parents as $parent): ?>
  <li><a href="<?= url::site("albums/{$parent->id}") ?>"><?= $parent->title ?></a></li>
  <? endforeach ?>
  <li class="active"><?= $item->title ?></li>
</ul>
<? endif ?>
