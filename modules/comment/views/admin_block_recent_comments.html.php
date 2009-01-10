<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <? foreach ($comments as $comment): ?>
  <li>
    <img width="40" height="40"
         src="<?= $comment->author()->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
         class="gAvatar" alt="<?= $comment->author_name() ?>" />
    <?= date("Y-M-d H:i:s", $comment->created) ?>
    <?= t("<a href=#>{{author_name}}</a> said <i>{{comment_text}}</i>",
          array("author_name" => $comment->author_name(),
                "comment_text" => text::limit_words($comment->text, 50))); ?>
  </li>
  <? endforeach ?>
</ul>
