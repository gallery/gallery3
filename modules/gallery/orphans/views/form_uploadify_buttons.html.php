<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="requires-flash">
  <!-- Proxy the done request back to our form, since its been ajaxified -->
  <button id="g-upload-done" class="ui-state-default ui-corner-all" onclick="$('#g-add-photos-form').submit();return false;">
  <?= t("Done") ?>
  </button>
  <button id="g-upload-cancel-all" class="ui-state-default ui-corner-all ui-state-disabled" onclick="$('#g-uploadify').uploadifyClearQueue();return false;" disabled="disabled">
  <?= t("Cancel uploads") ?>
  </button>
  <span id="g-add-photos-status-message" />
</div>
