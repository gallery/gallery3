<?php defined("SYSPATH") or die("No direct script access.") ?>
<form id="gSearchForm">
  <ul>
    <li>
      <label for="gSearch"><?= t("Search the gallery") ?></label>
      <input type="text" name="search" id="gSearch"/>
    </li>
    <li>
      <input type="submit" value="<?= t("Go") ?>" />
    </li>
  </ul>
</form>
