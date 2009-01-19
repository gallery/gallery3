<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title>Gallery3 Installer</title>
    <link rel="stylesheet" type="text/css" href="install.css"/>
  </head>
  <body>
    <div id="outer">
      <img src="http://www.gallery2.org/gallery2.png"/>
      <div id="inner">
        <?php if ($step == "already_installed"): ?>
        <p>
          Your Gallery3 install is complete.
        <?php elseif ($step == "welcome"): ?>
        <p>
          Welcome to Gallery 3.  In order to get started, we need to
          know how to talk to your database.  You'll need to know:
          <ol>
            <li> Database name </li>
            <li> Database username </li>
            <li> Database password </li>
            <li> Database host </li>
          </ol>

          If you're missing any of this information, please ask your
          web host or system administrator for a little help.
        </p>
        <p>
          <a href="index.php?step=get_info">Continue</a>
        </p>
        <?php elseif ($step == "get_info"): ?>
        <p>
          Enter your database connnection information here.  We've
          provided common values that work for most people.  If you
          have problems, contact your web host for help.
        </p>
        <form method="get" action="index.php">
          <fieldset>
            <ul>
              <li>
                Database Name: <input name="dbname" value="gallery3"/>
              </li>
              <li>
                User: <input name="dbuser" value="root"/>
              </li>
              <li>
                Password: <input name="dbpass" value=""/>
              </li>
              <li>
                Host: <input name="dbhost" value="localhost"/>
              </li>
            </ul>
            <input type="hidden" name="step" value="save_info"/>
            <input type="submit" value="Continue"/x>
          </fieldset>
        </form>
        <?php endif ?>
      </div>
      <p>
        Did something go wrong? Run
        a <a href="index.php?page=check">system check</a> to make sure
        that it's not an issue with your web host.
      </p>
      <p>
        Questions or problems?  Visit the <a href="http://gallery.menalto.com">Gallery Website</a>.
      </p>
    </div>
  </body>
</html>
