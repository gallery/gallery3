<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-error">
  <h1>
    <?php echo t("Dang...  Page not found!") ?>
  </h1>
  <?php if ($is_guest): ?>
    <h2>
      <?php echo t("Hey wait, you're not signed in yet!") ?>
    </h2>
    <p>
       <?php echo t("Maybe the page exists, but is only visible to authorized users.") ?>
       <?php echo t("Please sign in to find out.") ?>
    </p>
    <?php echo $login_form ?>
    <script type="text/javascript">
      $(document).ready(function() {
        $("#g-username").focus();
      });
    </script>
  <?php else: ?>
    <p>
      <?php echo t("Maybe the page exists, but is only visible to authorized users.") ?>
      <?php echo t("If you think this is an error, talk to your Gallery administrator!") ?>
    </p>
 <?php endif; ?>
</div>
