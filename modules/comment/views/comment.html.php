<? defined("SYSPATH") or die("No direct script access."); ?>
<li id="gComment-<?= $comment->id; ?>">
  <p>
    <a href="#" class="gAuthor"><?= $comment->author ?></a>
    <?= comment::format_elapsed_time($comment->created) ?>,
    <span class="gUnderstate"><?= strftime('%c', $comment->created) ?></span>
  </p>
  <div>
    <?= $comment->text ?>
  </div>
</li>
