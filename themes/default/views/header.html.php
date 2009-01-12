<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= $theme->header_top() ?>
<a href="<?= url::site("albums/1") ?>">
  <img id="gLogo" alt="<?= t("Logo") ?>" src="<?= $theme->url("images/logo.png") ?>" />
</a>

<div id="gSiteMenu">
<?= $theme->site_menu() ?>
</div>

<?= $theme->header_bottom() ?>

<? if ($page_type != "tag"): ?>
<ul id="gBreadcrumbs">
  <? foreach ($parents as $parent): ?>
  <li><a href="<?= url::site("albums/{$parent->id}?show=$item->id") ?>"><?= $parent->title ?></a></li>
  <? endforeach ?>
  <li class="active"><?= $item->title ?></li>
</ul>
<? endif ?>
