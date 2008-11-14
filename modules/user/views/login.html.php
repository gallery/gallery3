<? defined("SYSPATH") or die("No direct script access."); ?>
<form id="gLogin" action="<?= url::site("login/process") ?>">
  <fieldset>
    <legend><?= _("Login") ?></legend>
    <ul>
      <li>
        <label for="gUsername"><?= _("Username") ?></label>
        <input type="text" name="username" id="gUsername" />
      </li>
      <li>
        <label for="gPassword"><?= _("Password") ?></label>
        <input type="password" name="password" id="gPassword" />
      </li>
      <li>
        <input type="submit" value="<?= _("Login")?>" />
      </li>
    </ul>
    <div id="gLoginMessage" class="gStatus gError gDisplayNone">
    </div>
  </fieldset>
</form>
