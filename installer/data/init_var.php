<?php defined("SYSPATH") or die("No direct script access.");
function create_var_directories() {
  if (!@mkdir("resizes");) {
    throw new Exception("Unable to create directory 'resizes'");
  }
  if (!@mkdir("g3_installer");) {
    throw new Exception("Unable to create directory 'g3_installer'");
  }
  if (!@mkdir("modules");) {
    throw new Exception("Unable to create directory 'modules'");
  }
  if (!@mkdir("uploads");) {
    throw new Exception("Unable to create directory 'uploads'");
  }
  if (!@mkdir("logs");) {
    throw new Exception("Unable to create directory 'logs'");
  }
  if (!@mkdir("albums");) {
    throw new Exception("Unable to create directory 'albums'");
  }
  if (!@mkdir("thumbs");) {
    throw new Exception("Unable to create directory 'thumbs'");
  }
}