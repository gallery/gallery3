<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $("document").ready(function() {
    // using JS for adding link titles to avoid running t() for each tag
    $("#g-tag-admin .g-tag-name").attr("title", <?= t("Click to edit this tag")->for_js() ?>);
    $("#g-tag-admin .g-delete-link").attr("title", $(".g-delete-link:first span").html());

    // In-place editing for tag admin
    $(".g-editable").gallery_in_place_edit({
      form_url: <?= html::js_string(url::site("admin/tags/form_rename/__ID__")) ?>
    });
  });
</script>

<? $tags_per_column = $tags->count()/5 ?>
<? $column_tag_count = 0 ?>

<div class="g-block">
  <h1> <?= t("Manage tags") ?> </h1>

  <div class="g-block-content">
    <table id="g-tag-admin">
      <caption>
        <?= t2("There is one tag", "There are %count tags", $tags->count()) ?>
      </caption>
      <tr>
        <td>
        <? foreach ($tags as $i => $tag): ?>
          <? $current_letter = strtoupper(mb_substr($tag->name, 0, 1)) ?>

          <? if ($i == 0): /* first letter */ ?>
          <strong><?= html::clean($current_letter) ?></strong>
          <ul>
          <? elseif ($last_letter != $current_letter): /* new letter */ ?>
          </ul>
            <? if ($column_tag_count > $tags_per_column): /* new column */ ?>
              <? $column_tag_count = 0 ?>
        </td>
        <td>
            <? endif ?>
          <strong><?= html::clean($current_letter) ?></strong>
          <ul>
          <? endif ?>
              <li>
                <span class="g-editable g-tag-name" rel="<?= $tag->id ?>"><?= html::clean($tag->name) ?></span>
                <span class="g-understate">(<?= $tag->count ?>)</span>
                <a href="<?= url::site("admin/tags/form_delete/$tag->id") ?>"
                    class="g-dialog-link g-delete-link g-button">
                  <span class="ui-icon ui-icon-trash"><?= t("Delete this tag") ?></span></a>
              </li>
          <? $column_tag_count++ ?>
          <? $last_letter = $current_letter ?>
        <? endforeach ?>
          </ul>
        </td>
      </tr>
    </table>
  </div>
</div>
