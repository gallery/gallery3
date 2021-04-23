<?php defined("SYSPATH") or die("No direct script access.") ?>

<?php foreach ($blocks as $ref => $text): ?>
<li class="g-draggable" ref="<?= $ref ?>"><?= $text ?></li>
<?php endforeach ?>
