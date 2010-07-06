<?php defined("SYSPATH") or die("No direct script access.") ?>
<div>
<script type="text/javascript">
 var load_tree = function(target_id, locked) {
   var load_url = "<?= url::site("move/show_sub_tree/{$source->id}/__TARGETID__") ?>";
   var node = $("#node_" + target_id);
   $("#g-move .node a").removeClass("selected");
   node.find("a:first").addClass("selected");
   if (locked) {
     $("#g-move-button").attr("disabled", "disabled");
     $("#g-move form input[name=target_id]").attr("value", "");
   } else {
     $("#g-move-button").removeAttr("disabled");
     $("#g-move form input[name=target_id]").attr("value", target_id);
   }
   var sub_tree = $("#tree_" + target_id);
   if (sub_tree.length) {
     sub_tree.toggle();
   } else {
     $.get(load_url.replace("__TARGETID__", target_id), {},
           function(data) {
             node.html(data);
             node.find("a:first").addClass("selected");
           });
   }
 }
</script>
<h1 style="display:none" >
  <? if ($source->type == "photo"): ?>
  <?= t("Move this photo to a new album") ?>
  <? elseif ($source->type == "movie"): ?>
  <?= t("Move this movie to a new album") ?>
  <? elseif ($source->type == "album"): ?>
  <?= t("Move this album to a new album") ?>
  <? endif ?>
</h1>
<div id="g-move">
  <ul id="tree_0">
    <li id="node_1" class="node">
      <?= $tree ?>
    </li>
  </ul>
  <form method="post" action="<?= url::site("move/save/$source->id") ?>">
    <?= access::csrf_form_field() ?>
    <input type="hidden" name="target_id" value="" />
    <input type="submit" id="g-move-button" value="<?= t("Move")->for_html_attr() ?>"
       disabled="disabled" class="submit" />
  </form>
</div>
</div>
