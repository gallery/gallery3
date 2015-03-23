<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?php echo html::clean($subject) ?> </title>
  </head>
  <body>
    <h2> <?php echo html::clean($subject) ?> </h2>
    <table>
      <tr>
        <?php if ($original->title != $item->title): ?>
        <td><?php echo t("New title:") ?></td>
        <td><?php echo html::clean($item->title) ?></td>
        <?php else: ?>
        <td><?php echo t("Title:") ?></td>
        <td><?php echo html::clean($item->title) ?></td>
        <?php endif ?>
      </tr>
      <tr>
        <td><?php echo t("Url:") ?></td>
        <td><a href="<?php echo $item->abs_url() ?>"><?php echo $item->abs_url() ?></a></td>
      </tr>
      <?php if ($original->description != $item->description): ?>
      <tr>
        <td><?php echo t("New description:") ?></td>
        <td><?php echo html::clean($item->description) ?></td>
      </tr>
      <?php elseif (!empty($item->description)): ?>
      <tr>
        <td><?php echo t("Description:") ?></td>
        <td><?php echo html::clean($item->description) ?></td>
      </tr>
      <?php endif ?>
    </table>
  </body>
</html>
