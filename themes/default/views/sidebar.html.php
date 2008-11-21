<? defined("SYSPATH") or die("No direct script access."); ?>
<?= View::sidebar($theme) ?>
<? foreach ($theme->blocks() as $block): ?>
  <?= $block ?>
<? endforeach ?>
