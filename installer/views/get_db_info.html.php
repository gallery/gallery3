<?php defined("SYSPATH") or die("No direct script access.") ?>
<h1> Let's get going! </h1>
<p>
  Installing Gallery is easy.  We just need a place to put your photos
  and info about your MySQL database.
</p>


<fieldset class="<?= installer::var_writable() ? 'success' : 'error' ?>">
  <legend>Photo Storage</legend>
  <?php if (!installer::var_writable()): ?>
  <p>
    We're having trouble creating a place for your photos.  Can you
    help?  We need you to create a directory called <em>var</em> in
    your gallery3 directory.  This sample code works for most users.
    Run it in the gallery3 directory:
    <code>
      mkdir var<br>
      chmod 777 var
    </code>
    <a href="index.php">Check again</a>
  </p>
  <?php else: ?>
  <p>
    We've found a place to store your photos:
    <code class="location"> <?= htmlspecialchars(VARPATH, ENT_QUOTES, 'UTF-8', true) ?> </code>
  </p>
  <?php endif ?>
</fieldset>

<?php if (installer::var_writable()): ?>
<form method="post" action="index.php?step=save_db_info">
  <fieldset>
    <legend>Database</legend>
    <p>
      Gallery 3 needs a MySQL database.  The values provided work for
      most setups, so if you're confused try clicking <i>continue</i>.
    </p>
    <br/>
    <table id="db_info">
      <tr>
        <td>
          Database name
        </td>
        <td>
          <input name="dbname" value="gallery3"/>
        </td>
      </tr>
      <tr>
        <td>
          User
        </td>
        <td>
          <input name="dbuser" value="root"/>
        </td>
      </tr>
      <tr>
        <td>
          Password
        </td>
        <td>
          <input name="dbpass" value=""/>
        </td>
      </tr>
      <tr>
        <td>
          Host
        </td>
        <td>
          <input name="dbhost" value="localhost"/>
        </td>
      </tr>
      <tr>
        <td>
          Table prefix <span class="subtext">(optional)</span>
        </td>
        <td>
          <input name="prefix" value=""/>
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
<?php endif ?>
