<?
print($open);

// Not sure what to do with these, but at least show that we received them.
if ($class) {
  print "<!-- unused class in form.html.php: $class -->";
}
if ($title) {
  print "<!-- unused title in form.html.php: $title -->";
}

function DrawForm($inputs, $level=1) {
  $error_messages = array();
  $prefix = str_repeat("  ", $level);

  foreach ($inputs as $input) {
    if ($input->type == 'group') {
      print "$prefix<fieldset>\n";
      print "$prefix  <legend>$input->name</legend>\n";
      print "$prefix  <ul>\n";
      DrawForm($input->inputs, $level + 2);
      print "$prefix  </ul>\n";
      print "$prefix</fieldset>\n";
    } else {
      if ($input->error_messages()) {
        $error_messages = array_merge($error_messages, $input->error_messages());
        print "$prefix<li class=\"gError\">\n";
      } else {
        print "$prefix<li>\n";
      }
      if ($input->label()) {
        print $prefix . "  " . $input->label() . "\n";
      }
      print $prefix . "  " . $input->render() . "\n";
      print "$prefix</li>\n";
      if ($input->message()) {
        print "$prefix<li>\n";
        print $prefix . "  " . $input->message() . "\n";
        print "$prefix</li>\n";
      }
    }
  }
  if ($error_messages) {
    print "$prefix  <div class=\"gStatus gError\">\n";
    foreach ($error_messages as $message) {
      print "<p class=\"gError\">$message<p>";
    }
    print "$prefix  </div>\n";
  }
}
DrawForm($inputs);

print($close);
?>
