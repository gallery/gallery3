<?php defined("SYSPATH") or die("No direct script access.") ?>
<h1> Whoa there! </h1>

<p class="error">
  There are some problems with your web hosting environment
  that need to be fixed before you can successfully install
  Gallery 3.
</p>

<ul>
  <?php foreach ($errors as $error): ?>
  <li>
    <?php print $error ?>
  </li>
  <?php endforeach ?>
</ul>

<p>
  <a href="index.php">Check again</a>
</p>
