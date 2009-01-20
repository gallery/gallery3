<?php defined("SYSPATH") or die("No direct script access.") ?>
<h1> Success! </h1>
<p class="success">
  Your Gallery3 install is complete!
</p>

<?php if (!empty($user)): ?>
<h2> Before you start using it... </h2>
<p>
  We've created an account for you to use:
  <br/>
  username: <b><?php print $user ?></b>
  <br/>
  password: <b><?php print $password ?></b>
  <br/>
  <br/>
  Save this information in a safe place, or change your admin password
  right away!
</p>
<?php endif ?>

<h2> <a href="..">Start using Gallery</a> </h2>

