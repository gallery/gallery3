<? defined("SYSPATH") or die("No direct script access."); ?>
<ul id="gCommentThread">
  <? foreach (array_reverse($comments) as $index => $comment): ?>
  <li id="gComment-<?= $index; ?>" class="gComment <?= $index % 2 ? 'odd' : 'even' ?>">
    <p>
      <a href="#" class="gAuthor"><?= $comment->author ?></a>
      said <?= round((time() - $comment->datetime)/60) ?> minutes ago
      <span class="understate"><?= strftime('%c', $comment->datetime) ?></span>
    </p>
    <div>
      <?= $comment->text ?>
    </div>
  </li>
  <? endforeach; ?>
</ul>
