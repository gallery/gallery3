<?php defined("SYSPATH") or die("No direct script access.") ?>
<?
// This is the template for all HTML errors.  If you're throwing an exception and you want your
// error to appear differently, extend Kohana_Exception and specify a different template.

// Log validation exceptions to ease debugging
if ($e instanceof ORM_Validation_Exception) {
  Log::add("error", "Validation errors: " . print_r($e->validation->errors(), 1));
}

if (php_sapi_name() == "cli") {
  include Kohana::find_file("views", "error/cli.txt");
  return;
}

try {
  // Admins get a special error page
  $user = Identity::active_user();
  if ($user && $user->admin) {
    include Kohana::find_file("views", "error/admin.html");
    return;
  }
} catch (Exception $ignored) {
}

// Try to show a themed error page for 404 errors
if ($e instanceof HTTP_Exception_404) {
  if (Route::$controller == "file_proxy") {
    print "File not found";
  } else {
    $view = new View_Theme("required/page.html", "other", "error");
    $view->page_title = t("Dang...  Page not found!");
    $view->content = new View("error/404.html");
    $user = Identity::active_user();
    $view->content->is_guest = $user && $user->guest;
    if ($view->content->is_guest) {
      $view->content->login_form = new View("gallery/login_ajax.html");
      $view->content->login_form->form = Auth::get_login_form("login/auth_html");
    }
    print $view;
  }
  return;
}

header("HTTP/1.1 500 Internal Server Error");
include Kohana::find_file("views", "error/user.html");
