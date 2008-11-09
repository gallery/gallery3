<? defined("SYSPATH") or die("No direct script access."); ?>
<? foreach ($theme->blocks() as $block): ?>
  <?= $block ?>
<? endforeach ?>
