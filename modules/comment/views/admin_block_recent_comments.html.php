<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <? foreach ($comments as $comment): ?>
  <li>
    <?= date("Y-M-d H:i:s", $comment->created) ?>
    <? printf(_("%s said %s"), "<a href=\"#\">$comment->author</a>",
              "<i>\"" . text::limit_words($comment->text, 50) . "\"</i>"); ?>
  </li>
  <? endforeach ?>
</ul>
