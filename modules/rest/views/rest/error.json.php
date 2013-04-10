<?php defined("SYSPATH") or die("No direct script access.") ?>
<?
// Log error response to ease debugging
Log::instance()->add(Log::ERROR, "Rest error details: " . print_r($e->response, 1));
?>
<?= json_encode($e->response);