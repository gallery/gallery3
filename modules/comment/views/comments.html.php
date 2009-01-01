<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <? foreach ($comments as $comment): ?>
  <li id="gComment-<?= $comment->id; ?>">
    <p>
      <a href="#" class="gAuthor"><?= $comment->author ?></a>
      <?= date("Y-M-d H:i:s", $comment->created) ?>
    </p>
    <div>
      <?= $comment->text ?>
    </div>
  </li>
  <? endforeach ?>
</ul>
