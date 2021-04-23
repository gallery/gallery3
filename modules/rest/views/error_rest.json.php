<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php
// Log error response to ease debugging
Kohana_Log::add("error", "Rest error details: " . print_r($e->response, 1));
?>
<?= json_encode($e->response);