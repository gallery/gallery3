<? defined("SYSPATH") or die("No direct script access."); ?>
<fieldset id="gLogin">
  <legend>Login</legend>
  <ul>
    <li>
      <label for="username">Username</label>
      <input type="text" class="text" id="username" />
    </li>
    <li>
      <label for="password">Password</label>
      <input type="password" class="password" id="password" />
    </li>
    <li>
      <input type="submit" class="submit" value="<?= _("Login")?>" />
    </li>
    <? if (!empty($error_message)): ?>
      <li>
        <p class="error" id="login_message">
          <?= $error_message ?>
       </p>
      </li>
    <? endif;?>
  </ul>
</fieldset>
