<?php defined("SYSPATH") or die("No direct script access.") ?>
<li>
  <a <?= $menu->css_id ? "id='{$menu->css_id}'" : "" ?>
     class="g-ajax-link <?= $menu->css_class ?>"
     href="<?= $menu->url ?>"
     title="<?= $menu->label->for_html_attr() ?>"
     ajax_handler="<?= $menu->ajax_handler ?>">
    <?= $menu->label->for_html() ?>
  </a>
</li>
