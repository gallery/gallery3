<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var TAG_RENAME_URL = <?= html::js_string(url::site("admin/tags/rename/__ID__")) ?>;
  $("document").ready(function() {
    // using JS for adding link titles to avoid running t() for each tag
    $("#g-tag-admin .g-tag-name").attr("title", <?= t("Click to edit this tag")->for_js() ?>);
    $("#g-tag-admin .g-delete-link").attr("title", $(".g-delete-link:first span").html());

    // In-place editing for tag admin
    $(".g-editable").bind("click", editInPlace);
  });
  // make some values available within tag.js
  var csrf_token = "<?= $csrf ?>";
  var save_i18n = <?= html::js_string(t("save")->for_html_attr()) ?>;
  var cancel_i18n = <?= html::js_string(t("cancel")->for_html_attr()) ?>;
</script>

<? $tags_per_column = $tags->count()/5 ?>
<? $column_tag_count = 0 ?>

<div class="g-block">
  <h1> <?= t("Tag Admin") ?> </h1>

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
              <? if ($column_tag_count > $tags_per_column): /* new column */ ?>
                 </td>
                <td>
                <? $column_tag_count = 0 ?>
              <? endif ?>
              </ul>
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
          <? endforeach /* $tags */ ?>
          </ul>
        </td>
      </tr>
    </table>
  </div>
</div>
