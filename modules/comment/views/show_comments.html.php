<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gComments"><!-- BEGIN #gComments -->
  <? if ($comment_list): ?>
    <h2>Comments</h2>
    <?= $comment_list ?>
  <? endif ?>

  <?= $comment_form ?>
</div><!--  END #gComments -->
