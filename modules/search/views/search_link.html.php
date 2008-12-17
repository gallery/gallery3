<? defined("SYSPATH") or die("No direct script access."); ?>
<form id="gSearchForm">
  <ul>
    <li>
      <label for="gSearch"><?= _("Search the gallery") ?></label>
      <input type="text" name="search" id="gSearch"/>
    </li>
    <li>
      <input type="submit" value="<?= _("Go") ?>" />
    </li>
  </ul>
</form>
