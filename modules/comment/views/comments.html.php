<?php defined("SYSPATH") or die("No direct script access.") ?>
<? if (comment::can_comment()): ?>
<a href="<?= url::site("form/add/comments/{$item->id}") ?>#comment-form" id="g-add-comment"
   class="g-button ui-corner-all ui-icon-left ui-state-default">
  <span class="ui-icon ui-icon-comment"></span>
  <?= t("Add a comment") ?>
</a>
<? endif ?>

<div id="g-comment-detail">
  <? if (!$comments->count()): ?>
  <p class="g-no-comments">
    <? if (comment::can_comment()): ?>
    <?= t("No comments yet. Be the first to <a %attrs>comment</a>!",
          array("attrs" => html::mark_clean("href=\"" . url::site("form/add/comments/{$item->id}") . "\" class=\"showCommentForm\""))) ?>
    <? else: ?>
    <?= t("No comments yet.") ?>
    <? endif ?>
   </p>
  <ul>
    <li class="g-no-comments">&nbsp;</li>
  </ul>
  <? endif ?>

  <? if ($comments->count()): ?>
  <ul>
    <? foreach ($comments as $comment): ?>
    <li id="g-comment-<?= $comment->id ?>">
      <p class="g-author">
        <a href="#">
          <img src="<?= $comment->author()->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
               class="g-avatar"
               alt="<?= html::clean_attribute($comment->author_name()) ?>"
               width="40"
               height="40" />
        </a>
        <? if ($comment->author()->guest): ?>
        <?= t('on %date %name said',
            array("date" => gallery::date_time($comment->created),
                  "name" => html::clean($comment->author_name()))); ?>
        <? else: ?>
        <?= t('on %date <a href="%url">%name</a> said',
              array("date" => gallery::date_time($comment->created),
                    "url" => user_profile::url($comment->author_id),
                    "name" => html::clean($comment->author_name()))); ?>
        <? endif ?>
      </p>
      <div>
        <?= nl2br(html::purify($comment->text)) ?>
      </div>
    </li>
    <? endforeach ?>
  </ul>
  <? endif ?>
  <a name="comment-form" id="g-comment-form-anchor"></a>
</div>
