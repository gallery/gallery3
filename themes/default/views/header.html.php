<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= $theme->site_status() ?>
<?= $theme->header_top() ?>
<img id="gLogo" width="107" height="48" alt="<?= _("Logo") ?>" src="<?= $theme->url("images/logo.png") ?>" />

<div id="gSiteMenu">
<?= $theme->site_menu() ?>
</div>

<?= $theme->header_bottom() ?>

<? if ($page_type != "tag"): ?>
<ul id="gBreadcrumbs">
  <? foreach ($parents as $parent): ?>
  <li><a href="<?= url::site("albums/{$parent->id}") ?>"><?= $parent->title ?></a></li>
  <? endforeach ?>
  <li class="active"><?= $item->title ?></li>
</ul>
<? endif ?>
