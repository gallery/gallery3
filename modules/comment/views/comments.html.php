<?php defined("SYSPATH") or die("No direct script access.") ?>
<a name="comments"></a>
<ul id="gComments">
  <? foreach ($comments as $comment): ?>
  <li id="gComment-<?= $comment->id ?>">
    <p class="gAuthor">
      <a href="#">
        <img width="40" height="40"
             src="<?= $comment->author()->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
             class="gAvatar" alt="<?= $comment->author_name() ?>" />
      </a>
      <?= t("on {{date}} <a href=#>{{name}}</a> said",
            array("date" => date("Y-M-d H:i:s", $comment->created),
                  "name" => $comment->author_name())); ?>
    </p>
    <div>
      <?= $comment->text ?>
    </div>
  </li>
  <? endforeach ?>
</ul>
