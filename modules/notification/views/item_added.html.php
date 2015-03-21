<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?php echo  html::clean($subject) ?> </title>
  </head>
  <body>
    <h2><?php echo  html::clean($subject) ?></h2>
    <table>
      <tr>
        <td><?php echo  t("Title:") ?></td>
        <td><?php echo  html::purify($item->title) ?></td>
      </tr>
      <tr>
        <td><?php echo  t("Url:") ?></td>
        <td>
          <a href="<?php echo  $item->abs_url() ?>">
            <?php echo  $item->abs_url() ?>
          </a>
        </td>
      </tr>
      <?php if ($item->description): ?>
      <tr>
        <td><?php echo  t("Description:") ?></td>
         <td><?php echo  nl2br(html::purify($item->description)) ?></td>
      </tr>
      <?php endif ?>
    </table>
  </body>
</html>
