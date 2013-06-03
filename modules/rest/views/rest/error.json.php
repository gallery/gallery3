<?php defined("SYSPATH") or die("No direct script access.") ?>
<?
// If $e->rest_array is set:
//   $data = array("errors" => $e->rest_array)
// If not, but $message is neither null or an empty string:
//   $data = array("errors" => array("other" => (string)$message))
// If not:
//   $data = array()
$data = isset($e->rest_array) ? $e->rest_array :
  (empty((string)$message) ? array("other" => (string)$message) : array());

$data = empty($data) ? array() : array("errors" => $data);
?>
<?= json_encode($data) ?>
