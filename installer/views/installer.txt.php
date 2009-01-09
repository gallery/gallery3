<?php defined("SYSPATH") or die("No direct script access.");
function green_start() {
  return "\x1B[32m";
}

function color_end() {
  return "\x1B[0m";
}

function red_start() {
  return "\x1B[31m";
}

function magenta_start() {
  return "\x1B[35m";
}

function print_msg($header, $msg, $error) {
  $format = "| %-21.21s | %-81.81s |\n"; 
  foreach (explode("\n", wordwrap($msg, 72)) as $text) {
    if ($error) {
      printf($format, $header, red_start() . $text . color_end());
    } else {
      printf($format, $header, green_start() . $text . color_end());
    }
    $header = "";
  }
}

echo "+", str_repeat("-", 98), "+\n";
printf("| %-96.96s |\n", "Environment Tests");
printf("| %-96.96s |\n", "The following tests have been run to determine if Gallery3 will work " .
       "in your environment.");
printf("| %-96.96s |\n", "If any of the tests have failed, consult the documention on " .
       "http://gallery.menalto.com");
printf("| %-96.96s |\n", "for more information on how to correct the problem.");
echo "+", str_repeat("-", 98), "+\n";

foreach (self::$messages as $header => $msg) {
  print_msg($header, $msg["text"], $msg["error"]);  
}

echo "+", str_repeat("-", 98), "+\n";
flush();