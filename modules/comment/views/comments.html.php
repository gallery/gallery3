<?php defined("SYSPATH") or die("No direct script access.") ?>
<? if (!$comments->count()): ?>
<p id="gNoCommentsYet">
  <?= t("No comments yet. Be the first to <a %attrs>comment</a>!",
      array("attrs" => "href=\"#add_comment_form\" class=\"showCommentForm\"")) ?>
</p>
<? endif ?>
<ul id="gComments">
  <? foreach ($comments as $comment): ?>
  <li id="gComment-<?= $comment->id ?>">
    <p class="gAuthor">
      <a href="#">
        <img src="<?= $comment->author()->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
             class="gAvatar"
             alt="<?= SafeString::of($comment->author_name()) ?>"
             width="40"
             height="40" />
      </a>
      <?= t('on %date <a href="#">%name</a> said',
            array("date" => date("Y-M-d H:i:s", $comment->created),
                  "name" => SafeString::of($comment->author_name()))); ?>
    </p>
    <div>
      <?= nl2br(SafeString::purify($comment->text)) ?>
    </div>
  </li>
  <? endforeach ?>
</ul>
<a name="add_comment_form"></a>
