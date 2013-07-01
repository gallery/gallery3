<?php defined("SYSPATH") or die("No direct script access.") ?>
<? // @todo Set hover on AlbumGrid list items ?>
<form action="<?= URL::site("/search") ?>" id="g-search-form" class="g-short-form">
  <fieldset>
    <legend>
      <?= t("Search") ?>
    </legend>
    <ul>
      <li>
        <? if ($album->is_root()): ?>
          <label for="q"><?= t("Search the gallery") ?></label>
        <? else: ?>
          <label for="q"><?= t("Search this album") ?></label>
        <? endif; ?>
        <input name="album" type="hidden" value="<?= HTML::clean_attribute($album->id) ?>" />
        <input name="q" id="q" type="text" value="<?= HTML::clean_attribute($q) ?>" class="text" />
      </li>
      <li>
        <input type="submit" value="<?= t("Search")->for_html_attr() ?>" class="submit" />
      </li>
    </ul>
  </fieldset>
</form>

<div id="g-search-results">
  <h1><?= t("Search results") ?></h1>

  <? if ($album->is_root()): ?>
    <div>
      <?= t("Searched the whole gallery.") ?>
    </div>
  <? else: ?>
    <div>
      <?= t("Searched within album <b>%album</b>.", array("album" => HTML::purify($album->title))) ?>
      <a href="<?= URL::site("search") . URL::query(array("album" => null)) ?>"><?= t("Search whole gallery") ?></a>
    </div>
  <? endif; ?>

  <? if (count($children)): ?>
  <ul id="g-album-grid" class="ui-helper-clearfix">
    <? foreach ($children as $item): ?>
    <? $item_class = $item->is_album() ? "g-album" : "g-photo" ?>
    <li class="g-item <?= $item_class ?>">
      <a href="<?= $item->url() ?>">
        <?= $item->thumb_img(array("class" => "g-thumbnail")) ?>
        <p>
          <span class="<?= $item_class ?>"></span>
          <?= HTML::purify(Text::limit_chars($item->title, 32, "…")) ?>
        </p>
        <div>
          <?= nl2br(HTML::purify(Text::limit_chars($item->description, 64, "…"))) ?>
        </div>
      </a>
    </li>
    <? endforeach ?>
  </ul>
  <?= $theme->paginator() ?>

  <? else: ?>
  <p>
    <?= t("No results found for <b>%term</b>", array("term" => $q)) ?>
  </p>

  <? endif; ?>
</div>
