<?php defined("SYSPATH") or die("No direct script access.") ?>
<? if ($menu->is_root): ?>

<ul class="<?= $menu->css_class ?>">
  <? foreach ($this->elements as $element): ?>
  <?= $element->render() ?>
  <? endforeach ?>
</ul>

<? else: ?>

<li title="<?= $menu->label->for_html_attr() ?>">
  <a href="#">
    <?= $menu->label->for_html() ?>
  </a>
  <ul>
    <? foreach ($this->elements as $element): ?>
    <?= $element->render() ?>
    <? endforeach ?>
  </ul>
</li>

<? endif ?>
