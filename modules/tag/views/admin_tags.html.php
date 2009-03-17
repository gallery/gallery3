<?php defined("SYSPATH") or die("No direct script access.") ?>
<script>
  $("document").ready(function() {
    // $("#gTagAdmin .tag-name").attr("title", "<?= t("Click to edit this tag") ?>");
    $("#gTagAdmin .delete-link").attr("title", $(".delete-link:first span").html());
  });
</script>
<div class="gBlock">
  <h2>
    <?= t("Tag Admin") ?>
  </h2>

  <? $tags_per_column = $tags->count()/5 ?>
  <? $column_tag_count = 0 ?>

  <table id="gTagAdmin" class="gBlockContent">
    <caption class="understate">
      <?= t2("There is one tag", "There are %count tags", $tags->count()) ?>
    </caption>
    <tr>
      <td>
        <? foreach ($tags as $i => $tag): ?>
          <? $current_letter = strtoupper(substr($tag->name, 0, 1)) ?>

          <? if ($i == 0): /* first letter */ ?>
            <strong><?= $current_letter ?></strong>
            <ul>
          <? elseif ($last_letter != $current_letter): /* new letter */ ?>
            <? if ($column_tag_count > $tags_per_column): /* new column */ ?>
               </td>
              <td>
              <? $column_tag_count = 0 ?>
            <? endif ?>

            </ul>
            <strong><?= $current_letter ?></strong>
            <ul>
          <? endif ?>

          <li>
            <span id="gTag-<?= $tag->id ?>" class="gEditable tag-name"><?= $tag->name ?></span>
            <span class="understate">(<?= $tag->count ?>)</span>
            <a href="<?= url::site("admin/tags/form_delete/$tag->id") ?>"
               class="gDialogLink delete-link gButtonLink">
                <span class="ui-icon ui-icon-trash"><?= t("Delete this tag") ?></span></a>
          </li>

          <? $column_tag_count++ ?>
          <? $last_letter = $current_letter ?>
        <? endforeach /* $tags */ ?>
        </ul>
      </td>
    </tr>
  </table>
</div>
