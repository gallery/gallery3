<?php defined("SYSPATH") or die("No direct script access.") ?>
<li>
  <a <?php echo  $menu->css_id ? "id='{$menu->css_id}'" : "" ?>
     class="g-ajax-link <?php echo  $menu->css_class ?>"
     href="<?php echo  $menu->url ?>"
     title="<?php echo  $menu->label->for_html_attr() ?>"
     data-ajax-handler="<?php echo  $menu->ajax_handler ?>">
    <?php echo  $menu->label->for_html() ?>
  </a>
</li>
