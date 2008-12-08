<?php 
echo html::specialchars($error) . "\n";
echo html::specialchars($description) . "\n";
if ( ! empty($line) AND ! empty($file)) {
  echo $file . "[" . $line . "]:" . "\n";
}
echo $message . "\n";
if ( ! empty($trace)) {
  $trace = preg_replace(array('/<li>/', '/<(.*?)>/', '/&gt;/'), array("\t", '', '>'), $trace);
  echo Kohana::lang('core.stack_trace') . "\n";
  echo $trace . "\n";
}

