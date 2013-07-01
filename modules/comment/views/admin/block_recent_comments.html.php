<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <? foreach ($comments as $comment): ?>
  <li class="<?= Text::alternate("g-even", "g-odd") ?>">
    <img src="<?= $comment->author()->avatar_url(32) ?>"
         class="g-avatar"
         alt="<?= HTML::clean_attribute($comment->author_name()) ?>"
         width="32"
         height="32" />
    <?= Gallery::date_time($comment->created) ?>
    <? if ($comment->author()->guest): ?>
    <?= t('%author_name said <em>%comment_text</em>',
          array("author_name" => HTML::clean($comment->author_name()),
                "comment_text" => Text::limit_words(nl2br(HTML::purify($comment->text)), 50))); ?>
    <? else: ?>
    <?= t('<a href="%url">%author_name</a> said <em>%comment_text</em>',
          array("author_name" => HTML::clean($comment->author_name()),
                "url" => UserProfile::url($comment->author_id),
                "comment_text" => Text::limit_words(nl2br(HTML::purify($comment->text)), 50))); ?>
    <? endif ?>
  </li>
  <? endforeach ?>
</ul>
