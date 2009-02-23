<?php defined("SYSPATH") or die("No direct script access.") ?>
<? if (!$comments->count()): ?>
<p>
  <?= t("No comments yet. Be the first to") ?>
  <a href="#add_comment_form" class="showCommentForm"><?= t("comment") ?></a>!
</p>
<? endif ?>
<ul id="gComments">
  <? foreach ($comments as $comment): ?>
  <li id="gComment-<?= $comment->id ?>">
    <p class="gAuthor">
      <a href="#">
        <img src="<?= $comment->author()->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
             class="gAvatar"
             alt="<?= $comment->author_name() ?>"
             width="40"
             height="40" />
      </a>
      <?= t("on %date <a href=#>%name</a> said",
            array("date" => date("Y-M-d H:i:s", $comment->created),
                  "name" => $comment->author_name())); ?>
    </p>
    <div>
      <?= $comment->text ?>
    </div>
  </li>
  <? endforeach ?>
</ul>
<a name="add_comment_form"></a>
