<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php
echo $e->getMessage(), "\n";
echo $e->getFile(), ":", $e->getLine(), "\n";
echo $e->getTraceAsString(), "\n";

