<? defined("SYSPATH") or die("No direct script access."); ?>
<form id="gUser" action="<?= url::site("user/dispatch/$user_id") ?>">
  <fieldset>
    <legend><?=  $action ?></legend>
    <ul>
      <li>
        <label for="gUsername"><?= _("Username") ?></label>
        <input type="text" id="gUsername" />
        <span id="gUsername_error" class="gStatus gError gDisplayNone"></span>
      </li>
      <li>
        <label for="gPassword"><?= _("Password") ?></label>
        <input type="password" id="gPassword" />
        <span id="gPassword_error" class="gStatus gError gDisplayNone"></span>
      </li>
      <li>
        <label for="gPassword_confirm"><?= _("Confirm Password") ?></label>
        <input type="password" id="gPassword_confirm" />
      </li>
      <li>
        <label for="gEmail"><?= _("Password") ?></label>
        <input type="password" id="gEmail" />
        <span id="gEmail_error" class="gStatus gError gDisplayNone"></span>
      </li>
      <li>
        <label for="gEmail_confirm"><?= _("Confirm Email") ?></label>
        <input type="password" id="gEmaild_confirm" />
      </li>
      <li>
        <input type="submit" value="<?=$button_text?>" />
      </li>
    </ul>
  </fieldset>
</form>
