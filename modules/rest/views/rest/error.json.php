<?php defined("SYSPATH") or die("No direct script access.") ?>
<?
// If $e->rest_array is set:
//   $data = array("errors" => $e->rest_array)
// If not, but $message is neither null or an empty string:
//   $data = array("errors" => array("other" => (string)$message))
// If not:
//   $data = array()
if (!empty($e->rest_array)) {
  $data = $e->rest_array;
} else {
  $message = (string)$message;
  $data = empty($message) ? array() : array("other" => $message);
}

$data = empty($data) ? array() : array("errors" => $data);
?>
<?= json_encode($data) ?>
