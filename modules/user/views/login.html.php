<form id="gLogin">
  <label for="username">Username</label>
  <input type="text" class="text" id="username" />
  <label for="password">Password</label>
  <input type="password" class="password" id="password" />
  <input type="submit" class="submit" value="<?= _("Login")?>" />
  <? if (!empty($error_message)): ?>
    <p class="error">
      <?= $error_message ?>
    </p>
  <? endif;?>
</form>
