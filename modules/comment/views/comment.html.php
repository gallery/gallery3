<? defined("SYSPATH") or die("No direct script access."); ?>
<li id="gComment-<?= $comment->id; ?>">
  <p>
    <a href="#" class="gAuthor"><?= $comment->author ?></a>
    <?= comment::format_elapsed_time($comment->datetime) ?>,
    <span class="gUnderstate"><?= strftime('%c', $comment->datetime) ?></span>
  </p>
  <div>
    <?= $comment->text ?> 
  </div>
</li>
