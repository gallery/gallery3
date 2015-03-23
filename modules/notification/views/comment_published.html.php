<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?php echo html::clean($subject) ?> </title>
  </head>
  <body>
    <h2><?php echo html::clean($subject) ?></h2>
    <table>
      <tr>
        <td><?php echo t("Comment:") ?></td>
  <td><?php echo nl2br(html::purify($comment->text)) ?></td>
      </tr>
      <tr>
        <td><?php echo t("Author name:") ?></td>
        <td><?php echo html::clean($comment->author_name()) ?></td>
      </tr>
      <tr>
        <td><?php echo t("Author email:") ?></td>
        <td><?php echo html::clean($comment->author_email()) ?></td>
      </tr>
      <tr>
        <td><?php echo t("Author URL:") ?></td>
        <td><?php echo html::clean($comment->author_url()) ?></td>
      </tr>
      <tr>
        <td><?php echo t("Url:") ?></td>
        <td>
          <a href="<?php echo $comment->item()->abs_url() ?>#comments">
            <?php echo $comment->item()->abs_url() ?>#comments
          </a>
        </td>
      </tr>
    </table>
  </body>
</html>
