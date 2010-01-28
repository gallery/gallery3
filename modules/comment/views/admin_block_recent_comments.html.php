<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <? foreach ($comments as $comment): ?>
  <li class="<?= text::alternate("g-even", "g-odd") ?>">
    <img src="<?= $comment->author()->avatar_url(32, $theme->url("images/avatar.jpg", true)) ?>"
         class="g-avatar"
         alt="<?= html::clean_attribute($comment->author_name()) ?>"
         width="32"
         height="32" />
    <?= gallery::date_time($comment->created) ?>
    <? if ($comment->author()->guest): ?>
    <?= t('%author_name said <em>%comment_text</em>',
          array("author_name" => html::clean($comment->author_name()),
                "comment_text" => text::limit_words(nl2br(html::purify($comment->text)), 50))); ?>
    <? else: ?>
    <?= t('<a href="%url">%author_name</a> said <em>%comment_text</em>',
          array("author_name" => html::clean($comment->author_name()),
                "url" => user_profile::url($comment->author_id),
                "comment_text" => text::limit_words(nl2br(html::purify($comment->text)), 50))); ?>
    <? endif ?>
  </li>
  <? endforeach ?>
</ul>
