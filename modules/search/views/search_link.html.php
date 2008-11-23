<? defined("SYSPATH") or die("No direct script access."); ?>
<form id="gSearchForm">
  <ul>
    <li>
      <label for="search"><?= _("Search") ?></label>
      <input type="text" name="search" value="<?= _("Search Gallery ...") ?>"/>
    </li>
    <li>
      <input type="submit" value="<?= _("search") ?>" />
    </li>
  </ul>
</form>
