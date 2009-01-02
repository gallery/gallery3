<?php defined("SYSPATH") or die("No direct script access.");?>
<script type="text/javascript">
  $("document").ready(function() {
    ajaxify_spam_filter_form();
    $("#gContent #drivers").change(function() {
      data = $("#gContent #drivers :selected").text();
      $("#gContent #filter_data").load("<?= url::site("admin/spam_filter/callback") ?>",
          {driver: $("#gContent #drivers :selected").text(),
           csrf: "<?= access::csrf_token() ?>"});
    });
  });
  function ajaxify_spam_filter_form() {
    $("#gContent form").ajaxForm({
      dataType: "json",
      success: function(data) {
        if (data.form) {
          $("#gContent form").replaceWith(data.form);
          ajaxify_spam_filter_form();
        }
        if (data.result == "success") {
          window.location.reload();
        }
      }
    });
  };
</script>
<form action="<?= url::site("admin/spam_filter/edit") ?>" method="post" class="form">
  <?= access::csrf_form_field() ?>
  <fieldset>
    <legend><?= _("Configure Spam Filter") ?></legend>
    <ul>
      <li>
        <label for="drivers" >Available Drivers</label>
        <select id="drivers" name="drivers" class="dropdown" >
        <? foreach ($drivers as $index => $driver): ?>
          <option value="<?= $index ?>"<? if (!empty($driver["selected"])): ?> selected="selected"<? endif?>><?= $driver["name"]?></option>
        <? endforeach ?>
        </select>
      </li>
      <div id="filter_data" >
      <?= $filter_data ?>
      </div>
      <li>
        <button type="submit" class="submit" ><?= _("Configure") ?></button>
      </li>
    </ul>
  </fieldset>
</form>
