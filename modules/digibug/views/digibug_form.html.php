<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <?= html::script("lib/jquery.js") ?>
    <script type="text/javascript">
      $("body form").ready(function() {
        $("body form").submit();
      });
    </script>
  </head>
  <body>
    <?= form::open("http://www.digibug.com/dapi/order.php") ?>
    <?= form::hidden($order_parms) ?>
    <?= form::close() ?>
  </body>
</html>
