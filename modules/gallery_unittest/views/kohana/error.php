<?php defined("SYSPATH") or die("No direct script access.") ?>
<?
$message = (string)$e->getMessage();
if ($e instanceof HTTP_Exception) {
  // Makes first line look like "HTTP:404:message" or "HTTP:404" if no message found.
  echo "HTTP:", $e->getCode();
  if (strlen($message)) {
    echo ":";
  }
}
echo $message, "\n";
echo $e->getFile(), ":", $e->getLine(), "\n";
echo $e->getTraceAsString(), "\n";

