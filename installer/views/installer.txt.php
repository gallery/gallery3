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

foreach (self::$messages as $section) {
  echo "+", str_repeat("-", 98), "+\n";
  printf("| %-96.96s |\n", $section["header"]);
  foreach (explode("\n", wordwrap($section["description"], 92)) as $text) {
    printf("| %-96.96s |\n", $text);
  }
  echo "+", str_repeat("-", 98), "+\n";

  foreach ($section["msgs"] as $header => $msg) {
    print_msg($header, $msg["text"], $msg["error"]);  
  }
}

echo "+", str_repeat("-", 98), "+\n";
flush();