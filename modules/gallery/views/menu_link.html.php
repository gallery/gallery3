<?php defined("SYSPATH") or die("No direct script access.") ?>
<li>
  <a id="<?= $menu->css_id ?>"
     class="g-menu-link <?= $menu->css_class ?>"
     href="<?= $menu->url ?>"
     title="<?= $menu->label->for_html_attr() ?>">
    <?= $menu->label->for_html() ?>
  </a>
</li>
