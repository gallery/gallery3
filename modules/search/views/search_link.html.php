<?php defined("SYSPATH") or die("No direct script access.") ?>
<form action="<?= url::site("search") ?>" id="g-quick-search-form">
  <ul>
    <li>
      <label for="g-search"><?= t("Search the gallery") ?></label>
      <input type="text" name="q" id="g-search"/>
    </li>
    <li>
      <input type="submit" value="<?= t("Go")->for_html_attr() ?>" />
    </li>
  </ul>
</form>
