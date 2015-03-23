<?php defined("SYSPATH") or die("No direct script access.") ?>
<li id="g-comment-<?php echo $comment->id; ?>" class="g-comment-state-<?php echo $comment->state ?>">
  <p class="g-author">
    <a href="#">
      <img src="<?php echo $comment->author()->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
           class="g-avatar"
           alt="<?php echo html::clean_attribute($comment->author_name()) ?>"
           width="40"
           height="40" />
    </a>
    <?php if ($comment->author()->guest): ?>
    <?php echo t("on %date_time, %name said",
          array("date_time" => gallery::date_time($comment->created),
                "name" => html::clean($comment->author_name()))) ?>
    <?php else: ?>
    <?php echo t("on %date_time,  <a href=\"%url\">%name</a> said",
          array("date_time" => gallery::date_time($comment->created),
                "url" => user_profile::url($comment->author_id),
                "name" => html::clean($comment->author_name()))) ?>
    <?php endif ?>
  </p>
  <div>
  <?php echo nl2br(html::purify($comment->text)) ?>
  </div>
  <?php if ($comment->state == "unpublished"): ?>
  <b> <?php echo t("Your comment is held for moderation. The site moderator will review and publish it.") ?> </b>
  <?php endif ?>
</li>
