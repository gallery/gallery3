<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php
!file_exists(VARPATH . "albums") && mkdir(VARPATH . "albums");
!file_exists(VARPATH . "logs") && mkdir(VARPATH . "logs");
!file_exists(VARPATH . "modules") && mkdir(VARPATH . "modules");
!file_exists(VARPATH . "resizes") && mkdir(VARPATH . "resizes");
!file_exists(VARPATH . "thumbs") && mkdir(VARPATH . "thumbs");
!file_exists(VARPATH . "uploads") && mkdir(VARPATH . "uploads");
