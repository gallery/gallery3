<?php defined("SYSPATH") or die("No direct script access.") ?>
<form method="post" action="index.php?step=save_db_info">
  <fieldset>
    <legend>Database</legend>
    <p>
      We've provided values that work for most common web hosts.  If
      you have problems, contact your web host for help.
    </p>
    <br/>
    <table id="db_info">
      <tr>
        <td>
          Database Name
        </td>
        <td>
          <input name="dbname" value="<?= $dbname ?>"/>
        </td>
      </tr>
      <tr>
        <td>
          User
        </td>
        <td>
          <input name="dbuser" value="<?= $user ?>"/>
        </td>
      </tr>
      <tr>
        <td>
          Password
        </td>
        <td>
          <input name="dbpass" value="<?= $password ?>"/>
        </td>
      </tr>
      <tr>
        <td>
          Host
        </td>
        <td>
          <input name="dbhost" value="<?= $host ?>"/>
        </td>
      </tr>
      <tr>
        <td>
          Table Prefix
        </td>
        <td>
          <input name="prefix" value="<?= $prefix ?>"/>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <input type="submit" value="Continue"/>
        </td>
      </tr>
    </table>
  </fieldset>
</form>
