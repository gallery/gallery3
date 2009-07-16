<?php defined("SYSPATH") or die("No direct script access.") ?>
<h1> Welcome! </h1>
<p>
  Installing Gallery is very easy.  We just need to know how to talk
  to your MySQL database, and we need a place to store your photos on
  your web host.
</p>


<fieldset>
  <legend>Photo Storage</legend>
  <?php if (empty($writable)): ?>
  <p class="error">
    We're having trouble creating a place for your photos.  Can you
    help?  Please create a directory called "var" using <code>mkdir var</code> in your
    gallery3 directory, then run <code>chmod 777 var</code>.  That
    should fix it.
    <br/><br/>
    <a href="index.php">Check again</a>
    <br /><br/>
    <i>(Please fix the photo storage problem before continuing)</i>
  </p>
  <?php else: ?>
  <p class="success">
    We've found a place to store your photos:
    <code class="location"> <?= VARPATH ?> </code>
  </p>
  <?php endif ?>
</fieldset>

