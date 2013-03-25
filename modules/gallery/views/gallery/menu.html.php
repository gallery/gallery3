<?php defined("SYSPATH") or die("No direct script access.") ?>
<? if (!$menu->is_empty()): // Don't show the menu if it has no choices ?>
<? if ($menu->is_root): ?>
<ul <?= $menu->css_id ? "id='$menu->css_id'" : "" ?> class="<?= $menu->css_class ?>">
  <? foreach ($menu->elements as $element): ?>
  <?= $element->render() ?>
  <? endforeach ?>
</ul>

<? else: ?>

<li title="<?= $menu->label->for_html_attr() ?>">
  <a href="#">
    <?= $menu->label->for_html() ?>
  </a>
  <ul>
    <? foreach ($menu->elements as $element): ?>
    <?= $element->render() ?>
    <? endforeach ?>
  </ul>
</li>

<? endif ?>
<? endif ?>
