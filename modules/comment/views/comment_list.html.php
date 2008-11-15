<? defined("SYSPATH") or die("No direct script access."); ?>
<? foreach (array_reverse($comments) as $index => $comment): ?>
<li id="gComment-<?= $index; ?>" class="gComment <?= $index % 2 ? 'gOdd' : 'gEven' ?>">
  <p>
    <a href="#" class="gAuthor"><?= $comment->author ?></a>
    <?= comment::format_elapsed_time($comment->datetime) ?>,
    <span class="gUnderstate"><?= strftime('%c', $comment->datetime) ?></span>
  </p>
  <div>
    <?= $comment->text ?>
  </div>
</li>
<? endforeach; ?>
