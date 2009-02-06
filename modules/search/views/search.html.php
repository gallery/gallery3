<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gSearchForm">
  <form action="<?= url::site("/search") ?>">
    <fieldset>
      <legend>
        <?= t("Search") ?>
      </legend>
      <ul>
        <li>
          <input name="q" type="text" value="<?= $q ?>"/>
        </li>
        <li>
          <input type="submit"/>
        </li>
      </ul>
    </fieldset>
  </form>

  <ul>
    <? foreach ($items as $item): ?>
    <li>
      <a href="<?= url::site("items/$item->id") ?>">
        <?= $item->thumb_tag() ?>
        <p>
          <?= $item->title ?>
        </p>
        <p>
          <?= $item->description ?>
        </p>
      </a>
    </li>
    <? endforeach ?>
  </ul>
</div>
<?= $theme->pager() ?>
