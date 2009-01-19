<?php defined("SYSPATH") or die("No direct script access.");
if (!file_exists(VARPATH)) {
  if (!@mkdir(VARPATH)) {
    throw new Exception("Unable to create directory '" . VARPATH . "'");
  }
  chmod(VARPATH, 0777);
}
foreach (array("resizes", "modules", "uploads", "logs", "albums", "thumbs") as $dir) {
  if (!@mkdir("var/$dir")) {
    throw new Exception("Unable to create directory '$dir'");
  }
  chmod("var/$dir", 0777);
}