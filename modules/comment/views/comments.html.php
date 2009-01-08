<?php defined("SYSPATH") or die("No direct script access.") ?>
<a name="comments"></a>
<ul id="gComments">
  <? foreach ($comments as $comment): ?>
  <li id="gComment-<?= $comment->id; ?>">
    <? $avatar = $theme->url("images/avatar.jpg") ?>
    <? //if ($user->avatar($comment->author)): ?>
      <? //$avatar = $theme->url("images/avatar.jpg") ?>
    <? //endif ?>
    <p class="gAuthor">
      <a href="#"><img src="<?= $avatar ?>" class="gAvatar" alt="<?= $comment->author ?>" /></a>
      <?= t("on ") . date("Y-M-d H:i:s", $comment->created) ?>
      <a href="#"><?= $comment->author ?></a> <?= t("said") ?>
    </p>
    <div>
      <?= $comment->text ?>
    </div>
  </li>
  <? endforeach ?>
</ul>
