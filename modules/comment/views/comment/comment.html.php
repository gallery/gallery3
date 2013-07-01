<?php defined("SYSPATH") or die("No direct script access.") ?>
<li id="g-comment-<?= $comment->id; ?>">
  <p class="g-author">
    <a href="#">
      <img src="<?= $comment->author()->avatar_url(40) ?>"
           class="g-avatar"
           alt="<?= HTML::clean_attribute($comment->author_name()) ?>"
           width="40"
           height="40" />
    </a>
    <? if ($comment->author()->guest): ?>
    <?= t("on %date_time, %name said",
          array("date_time" => Gallery::date_time($comment->created),
                "name" => HTML::clean($comment->author_name()))) ?>
    <? else: ?>
    <?= t("on %date_time,  <a href=\"%url\">%name</a> said",
          array("date_time" => Gallery::date_time($comment->created),
                "url" => UserProfile::url($comment->author_id),
                "name" => HTML::clean($comment->author_name()))) ?>
    <? endif ?>
  </p>
  <div>
  <?= nl2br(HTML::purify($comment->text)) ?>
  </div>
</li>
