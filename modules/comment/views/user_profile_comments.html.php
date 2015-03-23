<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-comment-detail">
<ul>
  <?php foreach ($comments as $comment): ?>
  <li id="g-comment-<?php echo $comment->id ?>">
    <p class="g-author">
      <?php echo t("on %date for %title ",
            array("date" => gallery::date_time($comment->created),
                  "title" => $comment->item()->title)); ?>
      <a href="<?php echo $comment->item()->url() ?>">
        <?php echo $comment->item()->thumb_img(array(), 50) ?>
      </a>
    </p>
    <div>
      <?php echo nl2br(html::purify($comment->text)) ?>
    </div>
  </li>
  <?php endforeach ?>
</ul>
</div>
