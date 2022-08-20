<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php // @todo Set hover on AlbumGrid list items ?>
<form action="<?= url::site("/search") ?>" id="g-search-form" class="g-short-form">
  <fieldset>
    <legend>
      <?= t("Search") ?>
    </legend>
    <ul>
      <li>
        <?php if ($album->id == item::root()->id): ?>
          <label for="q"><?= t("Search the gallery") ?></label>
        <?php else: ?>
          <label for="q"><?= t("Search this album") ?></label>
        <?php endif; ?>
        <input name="album" type="hidden" value="<?= html::clean_attribute($album->id) ?>" />
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

  <?php if ($album->id == item::root()->id): ?>
    <div>
      <?= t("Searched the whole gallery.") ?>
    </div>
  <?php else: ?>
    <div>
      <?= t("Searched within album <b>%album</b>.", array("album" => html::purify($album->title))) ?>
      <a href="<?= url::site(url::merge(array("album" => item::root()->id))) ?>"><?= t("Search whole gallery") ?></a>
    </div>
  <?php endif; ?>

  <?php if (count($items)): ?>
  <ul id="g-album-grid" class="ui-helper-clearfix">
    <?php foreach ($items as $item): ?>
    <?php $item_class = $item->is_album() ? "g-album" : "g-photo" ?>
    <li class="g-item <?= $item_class ?>">
      <a href="<?= $item->url() ?>">
        <?= $item->thumb_img(array("class" => "g-thumbnail")) ?>
        <p>
          <span class="<?= $item_class ?>"></span>
          <?= html::purify(text::limit_chars($item->title, 32, "…")) ?>
        </p>
        <div>
          <?= nl2br(html::purify(text::limit_chars($item->description, 64, "…"))) ?>
        </div>
      </a>
    </li>
    <?php endforeach ?>
  </ul>
  <?= $theme->paginator() ?>

  <?php else: ?>
  <p>
    <?= t("No results found for <b>%term</b>", array("term" => $q)) ?>
  </p>

  <?php endif; ?>
</div>
