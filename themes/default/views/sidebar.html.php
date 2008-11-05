<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gSidebar" class="yui-b">
  <? foreach ($theme->blocks() as $block): ?>
    <?= $block ?>
  <? endforeach ?>
</div><!-- END #gSideBar -->
