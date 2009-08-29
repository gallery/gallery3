<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gBanner">
  <?= $theme->header_top() ?>
  <? if ($header_text = module::get_var("gallery", "header_text")): ?>
  <?= $header_text ?>
  <? else: ?>
  <a id="gLogo" href="<?= url::site("albums/1") ?>" title="<?= t("go back to the Gallery home") ?>">
    <img width="107" height="48" alt="<?= t("Gallery logo: Your photos on your web site") ?>" src="<?= $theme->url("images/logo.png") ?>" />
  </a>
  <? endif ?>
  <div id="gSiteMenu">
  <?= $theme->site_menu() ?>
  </div>
  <?= $theme->header_bottom() ?>
</div>

<? if (!empty($parents)): ?>
<ul class="gBreadcrumbs">
  <? foreach ($parents as $parent): ?>
  <li>
    <a href="<?= url::site("albums/{$parent->id}?show=$item->id") ?>">
      <?= SafeString::purify($parent->title) ?>
    </a>
  </li>
  <? endforeach ?>
  <li class="active"><?= SafeString::purify($item->title) ?></li>
</ul>
<? endif ?>
