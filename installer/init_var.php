<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php
!file_exists(VARPATH . "albums") && mkdir(VARPATH . "albums");
!file_exists(VARPATH . "logs") && mkdir(VARPATH . "logs");
!file_exists(VARPATH . "modules") && mkdir(VARPATH . "modules");
!file_exists(VARPATH . "resizes") && mkdir(VARPATH . "resizes");
!file_exists(VARPATH . "thumbs") && mkdir(VARPATH . "thumbs");
!file_exists(VARPATH . "tmp") && mkdir(VARPATH . "tmp");
!file_exists(VARPATH . "uploads") && mkdir(VARPATH . "uploads");
file_put_contents(VARPATH . "logs/.htaccess", base64_decode("RGlyZWN0b3J5SW5kZXggLmh0YWNjZXNzClNldEhhbmRsZXIgR2FsbGVyeV9TZWN1cml0eV9Eb19Ob3RfUmVtb3ZlCk9wdGlvbnMgTm9uZQo8SWZNb2R1bGUgbW9kX3Jld3JpdGUuYz4KUmV3cml0ZUVuZ2luZSBvZmYKPC9JZk1vZHVsZT4KT3JkZXIgYWxsb3csZGVueQpEZW55IGZyb20gYWxsCg=="));
file_put_contents(VARPATH . "tmp/.htaccess", base64_decode("RGlyZWN0b3J5SW5kZXggLmh0YWNjZXNzClNldEhhbmRsZXIgR2FsbGVyeV9TZWN1cml0eV9Eb19Ob3RfUmVtb3ZlCk9wdGlvbnMgTm9uZQo8SWZNb2R1bGUgbW9kX3Jld3JpdGUuYz4KUmV3cml0ZUVuZ2luZSBvZmYKPC9JZk1vZHVsZT4KT3JkZXIgYWxsb3csZGVueQpEZW55IGZyb20gYWxsCg=="));
file_put_contents(VARPATH . "uploads/.htaccess", base64_decode("RGlyZWN0b3J5SW5kZXggLmh0YWNjZXNzClNldEhhbmRsZXIgR2FsbGVyeV9TZWN1cml0eV9Eb19Ob3RfUmVtb3ZlCk9wdGlvbnMgTm9uZQo8SWZNb2R1bGUgbW9kX3Jld3JpdGUuYz4KUmV3cml0ZUVuZ2luZSBvZmYKPC9JZk1vZHVsZT4KT3JkZXIgYWxsb3csZGVueQpEZW55IGZyb20gYWxsCg=="));
