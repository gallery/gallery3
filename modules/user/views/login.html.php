<? defined("SYSPATH") or die("No direct script access."); ?>
<form id="gLogin" action="<?= url::site("login/process") ?>">
  <fieldset>
    <legend>Login</legend>
    <ul>
      <li>
        <label for="username">Username</label>
        <input type="text" id="gUsername" />
      </li>
      <li>
        <label for="password">Password</label>
        <input type="password" id="gPassword" />
      </li>
      <li>
        <input type="submit" class="submit" value="<?= _("Login")?>" />
      </li>
    </ul>
  </fieldset>
  <fieldset>
    <div class="gStatus gError gDisplayNone" id="gLoginMessage">
    </div>
  </fieldset>
</form>
