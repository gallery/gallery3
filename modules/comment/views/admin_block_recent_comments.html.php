<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <? foreach ($comments as $i => $comment): ?>
  <li class="<?= ($i % 2 == 0) ? "gEvenRow" : "gOddRow" ?>">
    <img src="<?= $comment->author()->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
         class="gAvatar"
         alt="<?= $comment->author_name() ?>"
         width="40"
         height="40" />
    <?= date("Y-M-d H:i:s", $comment->created) ?>
    <?= t("<a href=#>%author_name</a> said <em>%comment_text</em>",
          array("author_name" => $comment->author_name(),
                "comment_text" => text::limit_words($comment->text, 50))); ?>
  </li>
  <? endforeach ?>
</ul>
