<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-comment-detail">
<ul>
  <? foreach ($comments as $comment): ?>
  <li id="g-comment-<?= $comment->id ?>">
    <p class="g-author">
      <?= t("on %date for %title ",
            array("date" => gallery::date_time($comment->created),
                  "title" => $comment->item()->title)); ?>
      <a href="<?= $comment->item()->url() ?>">
        <?= $comment->item()->thumb_img(array(), 50) ?>
      </a>
    </p>
    <div>
      <?= nl2br(html::purify($comment->text)) ?>
    </div>
  </li>
  <? endforeach ?>
</ul>
</div>
