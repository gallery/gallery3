<? defined("SYSPATH") or die("No direct script access."); ?>
<?
print($open);

// Not sure what to do with these, but at least show that we received them.
if ($class) {
  print "<!-- unused class in form.html.php: $class -->";
}
if ($title) {
  print "<!-- unused title in form.html.php: $title -->";
}

if (!function_exists("DrawForm")) {
  function DrawForm($inputs, $level=1) {
    $error_messages = array();
    $prefix = str_repeat("  ", $level);

    foreach ($inputs as $input) {
      if ($input->type == 'group') {

        print "$prefix<fieldset>\n";
        print "$prefix  <legend>{$input->label}</legend>\n";
        print "$prefix  <ul>\n";

        DrawForm($input->inputs, $level + 2);
        print "$prefix  </ul>\n";

        // Since hidden fields can only have name and value attributes lets just render it now
        $hidden_prefix = "$prefix    ";
        foreach ($input->hidden as $hidden) {
          print "$prefix  {$hidden->render()}\n";
        }
        print "$prefix</fieldset>\n";
      } else {
        if ($input->error_messages()) {
          print "$prefix<li class=\"gError\">\n";
        } else {
          print "$prefix<li>\n";
        }

        if ($input->label()) {
          print "$prefix  {$input->label()}\n";
        }
        print "$prefix  {$input->render()}\n";
        if ($input->message()) {
          print "$prefix  <p>{$input->message()}</p>\n";
        }
        if ($input->error_messages()) {
          foreach ($input->error_messages() as $error_message) {
            print "$prefix  <p class=\"gError\">\n";
            print "$prefix    $error_message\n";
            print "$prefix  </p>\n";
          }
        }
        print "$prefix</li>\n";
      }
    }
  }
}
DrawForm($inputs);

print($close);
?>
