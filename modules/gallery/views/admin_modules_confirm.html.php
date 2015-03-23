<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="ui-helper-clearfix">
  <p>
    <?php echo t("The following issue(s) have been identified:") ?>
  </p>

  <div id="g-admin-modules-messages" class="g-block-content">
   <ul>
     <?php foreach (array("error" => "g-error", "warn" => "g-warning") as $type => $css_class): ?>
     <?php foreach ($messages[$type] as $message): ?>
     <li class="<?php echo $css_class ?>" style="padding-bottom: 0"><?php echo $message ?></li>
     <?php endforeach ?>
     <?php endforeach ?>
   </ul>
    <form method="post" action="<?php echo url::site("admin/modules/save") ?>">
      <?php echo access::csrf_form_field() ?>
      <?php foreach ($modules as $module): ?>
        <?php echo form::hidden($module, 1) ?>
      <?php endforeach ?>
    </form>
  </div>
</div>
