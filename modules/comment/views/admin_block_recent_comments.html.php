<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <? foreach ($comments as $comment): ?>
  <li>
    <?= date("Y-M-d H:i:s", $comment->created) ?>
    <?= t("{{author_name}} said {{comment_text}}",
          array("author_name" => "<a href=\"#\">$comment->author</a>",
                "comment_text" => "<i>\"" . text::limit_words($comment->text, 50) . "\"</i>")); ?>
  </li>
  <? endforeach ?>
</ul>
