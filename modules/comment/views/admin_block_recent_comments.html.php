<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <?php foreach ($comments as $comment): ?>
  <li class="<?php echo text::alternate("g-even", "g-odd") ?>">
    <img src="<?php echo $comment->author()->avatar_url(32, $theme->url("images/avatar.jpg", true)) ?>"
         class="g-avatar"
         alt="<?php echo html::clean_attribute($comment->author_name()) ?>"
         width="32"
         height="32" />
    <?php echo gallery::date_time($comment->created) ?>
    <?php if ($comment->author()->guest): ?>
    <?php echo t('%author_name said <em>%comment_text</em>',
          array("author_name" => html::clean($comment->author_name()),
                "comment_text" => text::limit_words(nl2br(html::purify($comment->text)), 50))); ?>
    <?php else: ?>
    <?php echo t('<a href="%url">%author_name</a> said <em>%comment_text</em>',
          array("author_name" => html::clean($comment->author_name()),
                "url" => user_profile::url($comment->author_id),
                "comment_text" => text::limit_words(nl2br(html::purify($comment->text)), 50))); ?>
    <?php endif ?>
  </li>
  <?php endforeach ?>
</ul>
