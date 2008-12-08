<?php
echo $error, "\n\n";
echo wordwrap($description, 80), "\n\n";
if (!empty($line) && !empty($file)) {
  echo $file, "[", $line, "]:\n";
}
echo $message, "\n\n";

if (!empty($trace)) {
  $trace = preg_replace(
    array('/<li>/', '/<(.*?)>/', '/&gt;/'),
    array("  ",     '',          '>'),
    $trace);
  echo "Stack Trace:\n";
  echo $trace, "\n";
}

