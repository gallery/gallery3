<p>
  Installing Gallery is very easy.  We just need to know how to talk
  to your MySQL database, and we need a place to store your photos on
  your web host.
</p>


<fieldset>
  <legend>Photo Storage</legend>
  <?php if (!installer::var_writable()): ?>
  <p class="error">
    We're having trouble creating a place for your photos.  Can you
    help?  Please create a directory called <code>var</code> in your
    gallery3 directory, then run <code>chmod 777 var</code>.  That
    should fix it.
    <br/><br/>
    <a href="index.php">Check again</a>
  </p>
  <?php else: ?>
  <p class="success">
    We've found a place to store your photos.
  </p>
  <?php endif ?>
</fieldset>

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
        <td colspan="2">
          <?php if (installer::var_writable()): ?>
          <input type="submit" value="Continue"/>
          <?php else: ?>
          <i class="error">(Please fix the photo storage problem before continuing)</i>
          <?php endif ?>
        </td>
      </tr>
    </table>
  </fieldset>
</form>
