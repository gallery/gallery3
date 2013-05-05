<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-rest-detail">
  <ul>
    <li id="g-rest-key">
      <p>
        <?= t("<b>Key</b>: %key", array("key" => $rest_key)) ?>
        <a class="g-button ui-state-default ui-corner-all g-dialog-link" href="<?= URL::site("rest/reset_api_key") ?>">
          <?= t("reset") ?>
        </a>
      </p>
    </li>
  </ul>
</div>
