<?php defined("SYSPATH") or die("No direct script access.") ?>
<li id="gComment-<?= $comment->id; ?>">
  <p class="gAuthor">
    <a href="#">
      <img src="<?= $comment->author()->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
           class="gAvatar"
           alt="<?= html::clean_attribute($comment->author_name()) ?>"
           width="40"
           height="40" />
    </a>
    <?= t("on %date_time, %author_name said",
          array("date_time" => gallery::date_time($comment->created),
                "author_name" => html::clean($comment->author_name()))) ?>
  </p>
  <div>
  <?= nl2br(html::purify($comment->text)) ?>
  </div>
</li>
