<? defined("SYSPATH") or die("No direct script access."); ?>
<form id="gLogin" action="<?= url::site("login/process") ?>">
  <fieldset>
    <legend>Login</legend>
    <ul class="gInline">
      <li>
        <label for="gUsername">Username</label>
        <input type="text" id="gUsername" />
      </li>
      <li>
        <label for="gPassword">Password</label>
        <input type="password" id="gPassword" />
      </li>
      <li>
        <input type="submit" value="<?= _("Login")?>" />
      </li>
    </ul>
    <div id="gLoginMessage" class="gStatus gError gDisplayNone">
    </div>
  </fieldset>
</form>
