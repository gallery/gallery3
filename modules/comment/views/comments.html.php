<?php defined("SYSPATH") or die("No direct script access.") ?>
  <a href="<?= url::site("form/add/comments/{$item->id})") ?>" id="gAddCommentButton"
   class="gButtonLink ui-corner-all ui-icon-left ui-state-default right">
  <span class="ui-icon ui-icon-comment"></span>
  <?= t("Add a comment") ?>
</a>
<div id="gCommentDetail">
<? if (!$comments->count()): ?>
<p id="gNoCommentsYet">
  <?= t("No comments yet. Be the first to <a %attrs>comment</a>!",
        array("attrs" => html::mark_clean("href=\"#add_comment_form\" class=\"showCommentForm\""))) ?>
</p>
<? endif ?>
<ul>
  <? foreach ($comments as $comment): ?>
  <li id="gComment-<?= $comment->id ?>">
    <p class="gAuthor">
      <a href="#">
        <img src="<?= $comment->author()->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
             class="gAvatar"
             alt="<?= html::clean_attribute($comment->author_name()) ?>"
             width="40"
             height="40" />
      </a>
      <?= t('on %date <a href="#">%name</a> said',
            array("date" => date("Y-M-d H:i:s", $comment->created),
                  "name" => html::clean($comment->author_name()))); ?>
    </p>
    <div>
      <?= nl2br(html::purify($comment->text)) ?>
    </div>
  </li>
  <? endforeach ?>
</ul>
</div>
