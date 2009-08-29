<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= SafeString::of($subject) ?> </title>
  </head>
  <body>
    <h2><?= SafeString::of($subject) ?></h2>
    <table>
      <tr>
        <td><?= t("Comment:") ?></td>
  <td><?= nl2br(SafeString::purify($comment->text)) ?></td>
      </tr>
      <tr>
        <td><?= t("Author Name:") ?></td>
        <td><?= SafeString::of($comment->author_name()) ?></td>
      </tr>
      <tr>
        <td><?= t("Author Email:") ?></td>
        <td><?= SafeString::of($comment->author_email()) ?></td>
      </tr>
      <tr>
        <td><?= t("Author URL:") ?></td>
        <td><?= SafeString::of($comment->author_url()) ?></td>
      </tr>
      <tr>
        <td><?= t("Url:") ?></td>
        <td>
          <a href="<?= $comment->item()->url(array(), true) ?>#comments">
            <?= $comment->item()->url(array(), true) ?>#comments
          </a>
        </td>
      </tr>
    </table>
  </body>
</html>
