<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= json_encode(isset($e->message_array) ? $e->message_array : array()) ?>
