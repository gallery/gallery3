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

print form_helper::Draw_Form($inputs);

print($close);
?>
