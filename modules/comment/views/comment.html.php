<?php defined("SYSPATH") or die("No direct script access.") ?>
<li id="g-comment-<?= $comment->id; ?>" class="g-comment-state-<?= $comment->state ?>">
  <p class="g-author">
    <a href="#">
      <img src="<?= $comment->author()->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
           class="g-avatar"
           alt="<?= html::clean_attribute($comment->author_name()) ?>"
           width="40"
           height="40" />
    </a>
    <?php if ($comment->author()->guest): ?>
    <?= t("on %date_time, %name said",
          array("date_time" => gallery::date_time($comment->created),
                "name" => html::clean($comment->author_name()))) ?>
    <?php else: ?>
    <?= t("on %date_time,  <a href=\"%url\">%name</a> said",
          array("date_time" => gallery::date_time($comment->created),
                "url" => user_profile::url($comment->author_id),
                "name" => html::clean($comment->author_name()))) ?>
    <?php endif ?>
  </p>
  <div>
  <?= nl2br(html::purify($comment->text)) ?>
  </div>
  <?php if ($comment->state == "unpublished"): ?>
  <b> <?= t("Your comment is held for moderation. The site moderator will review and publish it.") ?> </b>
  <?php endif ?>
</li>
