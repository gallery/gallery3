<?php defined("SYSPATH") or die("No direct script access.") ?>
<?
// This is the template for all HTML errors.  If you're throwing an exception and you want your
// error to appear differently, extend Kohana_Exception and specify a different template.

// Log validation exceptions to ease debugging
if ($e instanceof ORM_Validation_Exception) {
  Kohana_Log::add("error", "Validation errors: " . print_r($e->validation->errors(), 1));
}

if (php_sapi_name() == "cli") {
  include Kohana::find_file("views", "error_cli.txt");
  return;
}

try {
  // Admins get a special error page
  $user = identity::active_user();
  if ($user && $user->admin) {
    include Kohana::find_file("views", "error_admin.html");
    return;
  }
} catch (Exception $ignored) {
}

// Try to show a themed error page for 404 errors
if ($e instanceof Kohana_404_Exception) {
  if (Router::$controller == "file_proxy") {
    print "File not found";
  } else {
    $view = new Theme_View("page.html", "other", "error");
    $view->page_title = t("Dang...  Page not found!");
    $view->content = new View("error_404.html");
    $user = identity::active_user();
    $view->content->is_guest = $user && $user->guest;
    if ($view->content->is_guest) {
      $view->content->login_form = new View("login_ajax.html");
      $view->content->login_form->form = auth::get_login_form("login/auth_html");
    }
    print $view;
  }
  return;
}

header("HTTP/1.1 500 Internal Server Error");
include Kohana::find_file("views", "error_user.html");
