<?php defined("SYSPATH") or die("No direct script access.") ?>

<? foreach ($blocks as $ref => $text): ?>
<li class="gDraggable" ref="<?= $ref ?>"><?= $text ?></li>
<? endforeach ?>
