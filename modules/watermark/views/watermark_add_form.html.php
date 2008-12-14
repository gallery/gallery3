<? defined("SYSPATH") or die("No direct script access."); ?>
<? if (empty($_FILES)): ?>
<script>
$("document").ready(function() {
  ajaxify_form();
});

function ajaxify_form() {
  $("#gUploadWatermarkForm").ajaxForm({
    complete:function(xhr, statusText) {
      $("#gUploadWatermarkForm").replaceWith(xhr.responseText);
      if (xhr.status == 200) {
        $("#gUploadWatermarkForm").clearForm();
      }
      ajaxify_form();
    }
  });
}
</script>
<? endif ?>
<form action="<?= url::site("watermark/load")?>" method="post" id="gUploadWatermarkForm"
      enctype="multipart/form-data">
  <fieldset>
    <legend><?= _("Upload Watermark")?></legend>
    <ul>
      <li <? if (isset($errors["file"])): ?>class ="gError"<? endif ?>>
        <label for="file"><?= _("File")?></label>
        <input type="file" id="file" name="file" class="upload" 
          value="<?= $fields["file"]  ?>">
        <? if (isset($errors["file"]["type"])): ?>
        <p><?= _("Invalid File Type") ?></p>
        <? elseif (isset($errors["file"]["size"])): ?>
        <p><?= _("File too large") ?></p>
        <? elseif (isset($errors["file"]["valid"])): ?>
        <p><?= _("Upload failed") ?></p>
        <? endif ?>
        <? if (isset($success)): ?>
        <p><?= $success ?></p>
        <? endif ?>
      </li>
      <li>
        <button type="submit" class="submit"><?= _("Upload") ?></button>
      </li>
    </ul>
  </fieldset>
</form>