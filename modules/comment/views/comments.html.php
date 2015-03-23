<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php if (comment::can_comment()): ?>
<a href="<?php echo url::site("form/add/comments/{$item->id}") ?>#comment-form" id="g-add-comment"
   class="g-button ui-corner-all ui-icon-left ui-state-default">
  <span class="ui-icon ui-icon-comment"></span>
  <?php echo t("Add a comment") ?>
</a>
<?php endif ?>

<div id="g-comment-detail">
  <?php if (!$comments->count()): ?>
  <p class="g-no-comments">
    <?php if (comment::can_comment()): ?>
    <?php echo t("No comments yet. Be the first to <a %attrs>comment</a>!",
          array("attrs" => html::mark_clean("href=\"" . url::site("form/add/comments/{$item->id}") . "\" class=\"showCommentForm\""))) ?>
    <?php else: ?>
    <?php echo t("No comments yet.") ?>
    <?php endif ?>
   </p>
  <ul>
    <li class="g-no-comments">&nbsp;</li>
  </ul>
  <?php endif ?>

  <?php if ($comments->count()): ?>
  <ul>
    <?php foreach ($comments as $comment): ?>
    <li id="g-comment-<?php echo $comment->id ?>" class="g-comment-state-<?php echo $comment->state ?>">
      <p class="g-author">
        <a href="#">
          <img src="<?php echo $comment->author()->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
               class="g-avatar"
               alt="<?php echo html::clean_attribute($comment->author_name()) ?>"
               width="40"
               height="40" />
        </a>
        <?php if ($comment->author()->guest): ?>
        <?php echo t('on %date %name said',
            array("date" => gallery::date_time($comment->created),
                  "name" => html::clean($comment->author_name()))); ?>
        <?php else: ?>
        <?php echo t('on %date <a href="%url">%name</a> said',
              array("date" => gallery::date_time($comment->created),
                    "url" => user_profile::url($comment->author_id),
                    "name" => html::clean($comment->author_name()))); ?>
        <?php endif ?>
      </p>
      <div>
        <?php echo nl2br(html::purify($comment->text)) ?>
      </div>
      <?php if ($comment->state == "unpublished"): ?>
      <b> <?php echo t("Your comment is held for moderation. The site moderator will review and publish it.") ?> </b>
      <?php endif ?>
    </li>
    <?php endforeach ?>
  </ul>
  <?php endif ?>
  <a name="comment-form" id="g-comment-form-anchor"></a>
</div>
