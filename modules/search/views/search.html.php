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
        <input name="q" id="q" type="text" value="<?= html::clean_attribute($q) ?>" class="text" />
      </li>
      <li>
        <input type="submit" value="<?= t("Search")->for_html_attr() ?>" class="submit" />
      </li>
    </ul>
  </fieldset>
</form>

<div id="g-search-results">
  <h1><?= t("Search results") ?></h1>

  <? if (count($items)): ?>
  <ul id="g-album-grid" class="ui-helper-clearfix">
    <? foreach ($items as $item): ?>
    <? $item_class = $item->is_album() ? "g-album" : "g-photo" ?>
    <li class="g-item <?= $item_class ?>">
      <a href="<?= $item->url() ?>">
        <?= $item->thumb_img() ?>
        <p>
          <?= html::purify(text::limit_chars($item->title, 32, "…")) ?>
         </p>
         <div>
          <?= nl2br(html::purify(text::limit_chars($item->description, 64, "…"))) ?>
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
