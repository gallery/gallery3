<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gLoginMenu">
  <? if ($logged_in == false): ?>
    <a href="<?=url::site("user/register") ?>"><?= _("Register") ?></a> |
    <a href="#" id="login">Login</a>

    <!-- @todo need a better way to get the javascript into the page. -->
    <script type="text/javascript" src="<?=url::base() . "modules/auth/js/login.js" ?>"></script>
    <!-- @todo integrate this into the theme. -->
    <link rel="stylesheet" type="text/css" href="<?=url::base() . "modules/auth/css/login.css" ?>" media="screen,print,projection" />
    <div id="gLoginPopup">
      <a id="gLoginPopupClose">x</a>
      <form id="gLogin" style="display:none;">
        <label for="username">Username</label>
        <input type="text" class="text" id="username" />
        <label for="password">Password</label>
        <input type="password" class="password" id="password" />
        <input type="submit" class="submit" value="<?= url::site("auth/login") ?>" />
      </form>
    </div>
  <? else: ?>
    <a href="<?=url::site("auth/logout") ?>"><?= _("Logout") ?></a>
  <? endif ?>


</div>