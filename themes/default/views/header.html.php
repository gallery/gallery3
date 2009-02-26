<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= $theme->header_top() ?>
<? if ($header_text = module::get_var("core", "header_text")): ?>
<?= $header_text ?>
<? else: ?>
<a href="<?= url::site("albums/1") ?>">
  <img id="gLogo" alt="<?= t("Logo") ?>" src="<?= $theme->url("images/logo.png") ?>" />
</a>
<? endif ?>

<div id="gSiteMenu" style="display: none">
<?= $theme->site_menu() ?>
</div>

<?= $theme->header_bottom() ?>

<? if (!empty($parents)): ?>
<ul class="gBreadcrumbs">
  <? foreach ($parents as $parent): ?>
  <li><a href="<?= url::site("albums/{$parent->id}?show=$item->id") ?>"><?= $parent->title ?></a></li>
  <? endforeach ?>
  <li class="active"><?= $item->title ?></li>
</ul>
<? endif ?>
