<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
 var load_tree = function(target_id, locked) {
   var load_url = "<?= url::site("move/show_sub_tree/{$source->id}/__TARGETID__") ?>";
   var node = $("#node_" + target_id);
   $("#gMove .node a").removeClass("selected");
   node.find("a:first").addClass("selected");
   if (locked) {
     $("#gMoveButton").attr("disabled", "disabled");
     $("#gMove form input[name=target_id]").attr("value", "");
   } else {
     $("#gMoveButton").removeAttr("disabled");
     $("#gMove form input[name=target_id]").attr("value", target_id);
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
<h1 style="display: none">
  <? if ($source->type == "photo"): ?>
  <? t("Move this photo to a new album") ?>
  <? elseif ($source->type == "movie"): ?>
  <? t("Move this movie to a new album") ?>
  <? elseif ($source->type == "album"): ?>
  <? t("Move this album to a new album") ?>
  <? endif ?>
</h1>
<div id="gMove">
  <ul id="tree_0">
    <li id="node_1" class="node">
      <?= $tree ?>
    </li>
  </ul>
  <form method="post" action="<?= url::site("move/save/$source->id") ?>">
    <?= access::csrf_form_field() ?>
    <input type="hidden" name="target_id" value="" />
    <input type="submit" id="gMoveButton" value="<?= t("Move") ?>" disabled="disabled"/>
  </form>
</div>
