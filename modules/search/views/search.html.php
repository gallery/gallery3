<?php defined("SYSPATH") or die("No direct script access.") ?>
<? // @todo Set hover on AlbumGrid list items ?>
<form action="<?= url::site("/search") ?>" id="g-search-form" class="g-short-form">
  <fieldset>
    <legend>
      <?= t("Search") ?>
    </legend>
    <ul>
      <li>
        <label for="q"><?= t("Search the gallery") ?></label>
        <input name="q" id="q" type="text" value="<?= html::clean_attribute($q) ?>"/>
      </li>
      <li>
        <input type="submit" value="<?= t("Search")->for_html_attr() ?>" />
      </li>
    </ul>
  </fieldset>
</form>

<div id="g-search-results">
  <h1><?= t("Search results") ?></h1>

  <? if (count($items)): ?>
  <ul id="g-album-grid" class="ui-helper-clearfix">
    <? foreach ($items as $item): ?>
      <? $item_class = "g-photo"; ?>
      <? if ($item->is_album()): ?>
        <? $item_class = "g-album"; ?>
      <? endif ?>
   <li class="g-item <?= $item_class ?>">
      <a href="<?= $item->url() ?>">
        <?= $item->thumb_img() ?>
        <p>
    <?= html::purify($item->title) ?>
        </p>
        <div>
    <?= nl2br(html::purify($item->description)) ?>
        </div>
      </a>
    </li>
    <? endforeach ?>
  </ul>
  <?= $theme->pager() ?>

  <? else: ?>
  <p>
    <?= t("No results found for <b>%term</b>", array("term" => $q)) ?>
  </p>

  <? endif; ?>
</div>
