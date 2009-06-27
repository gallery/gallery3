<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <body>
    <?= form::open("http://www.digibug.com/dapi/order.php") ?>
    <?= form::hidden($order_parms) ?>
    <?= form::close() ?>
    <script type="text/javascript">
      document.forms[0].submit();
    </script>
  </body>
</html>
