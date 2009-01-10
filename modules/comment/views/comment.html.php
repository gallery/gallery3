<?php defined("SYSPATH") or die("No direct script access.") ?>
<li id="gComment-<?= $comment->id; ?>">
  <p class="gAuthor">
    <a href="#">
      <img width="40" height="40" src="<?= $user->avatar_url(40) ?>"
           class="gAvatar" alt="<?= $comment->author_name() ?>" />
    </a>
    <?= t("on ") . date("Y-M-d H:i:s", $comment->created) ?>
    <a href="#"><?= $comment->author_name() ?></a> <?= t("said") ?>
  </p>
  <div>
    <?= $comment->text ?>
  </div>
</li>
