<?php defined("SYSPATH") or die("No direct script access."); ?>

<? if ($root): ?>
  <script>
    var simpleTree_Rearrange;
    $(document).ready(function() {
      simpleTree_Rearrange = $(".simpleTree").simpleTree({
        autoclose: false,
        animate: true,
        afterClick:function(node) {
          alert("text-"+$('span:first', node).text());
        },
        afterDblClick:function(node) {
          alert("text-"+$('span:first', node).text());
        },
        afterMove:function(destination, source, pos) {
          alert("destination: "+destination.attr('id') + "\n" +
                "source: "+source.attr('id') + "\n" +
                "pos: " + pos);
        },
        afterAjax:function() {
        }
      });
    });
  </script>
  <ul class="simpleTree">
    <li class="root" id="<?= $root->id?>"><span><?= $root->title?></span>
<? endif; ?>
<ul>
  <? foreach ($children as $child): ?>
    <li id="<?= $child->id?>"><span class="text"><?= $child->title?></span>
      <? if ($child->type == "album"): ?>
        <ul class="ajax">
          <li><?= url::site("rearrange/$child->id") ?></li>
        </ul>
      <? endif; ?>
    </li>
  <? endforeach;?>
</ul>
<? if ($root): ?>
    </li>
  </ul>
<? endif; ?>

