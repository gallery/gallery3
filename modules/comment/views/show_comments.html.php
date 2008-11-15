<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gComments">
  <? if ($comment_list): ?>
    <h2><?= _("Comments") ?></h2>
    <ul id="gCommentThread">
      <?= $comment_list ?>
    </ul>
  <? endif ?>

  <?= $comment_form ?>
</div>
