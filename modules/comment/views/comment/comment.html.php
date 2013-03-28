<?php defined("SYSPATH") or die("No direct script access.") ?>
<li id="g-comment-<?= $comment->id; ?>">
  <p class="g-author">
    <a href="#">
      <img src="<?= $comment->author()->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
           class="g-avatar"
           alt="<?= html::clean_attribute($comment->author_name()) ?>"
           width="40"
           height="40" />
    </a>
    <? if ($comment->author()->guest): ?>
    <?= t("on %date_time, %name said",
          array("date_time" => gallery::date_time($comment->created),
                "name" => html::clean($comment->author_name()))) ?>
    <? else: ?>
    <?= t("on %date_time,  <a href=\"%url\">%name</a> said",
          array("date_time" => gallery::date_time($comment->created),
                "url" => user_profile::url($comment->author_id),
                "name" => html::clean($comment->author_name()))) ?>
    <? endif ?>
  </p>
  <div>
  <?= nl2br(html::purify($comment->text)) ?>
  </div>
</li>
