<?php defined("SYSPATH") or die("No direct script access.") ?>
<a name="comments"></a>
<ul id="gComments">
  <? foreach ($comments as $comment): ?>
  <li id="gComment-<?= $comment->id ?>">
    <p class="gAuthor">
      <a href="#">
        <img src="<?= $theme->url("images/avatar.jpg") ?>"
             class="gAvatar" alt="<?= $comment->author_name() ?>" />
      </a>
      <? printf(t("on %s <a href=#>%s</a> said"), date("Y-M-d H:i:s", $comment->created), $comment->author_name()) ?>
    </p>
    <div>
      <?= $comment->text ?>
    </div>
  </li>
  <? endforeach ?>
</ul>
