<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php if (!$menu->is_empty()): // Don't show the menu if it has no choices ?>
<?php if ($menu->is_root): ?>
<ul <?= $menu->css_id ? "id='$menu->css_id'" : "" ?> class="<?= $menu->css_class ?>">
  <?php foreach ($menu->elements as $element): ?>
  <?= $element->render() ?>
  <?php endforeach ?>
</ul>

<?php else: ?>

<li title="<?= $menu->label->for_html_attr() ?>">
  <a href="#">
    <?= $menu->label->for_html() ?>
  </a>
  <ul>
    <?php foreach ($menu->elements as $element): ?>
    <?= $element->render() ?>
    <?php endforeach ?>
  </ul>
</li>

<?php endif ?>
<?php endif ?>
