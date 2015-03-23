<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?php echo html::clean($subject) ?> </title>
  </head>
  <body>
    <h2><?php echo html::clean($subject) ?></h2>
    <table>
      <tr>
        <td colspan="2">
          <?php echo t("To view the changed album %title use the link below.",
              array("title" => html::purify($item->parent()->title))) ?>
        </td>
      </tr>
      <tr>
        <td><?php echo t("Url:") ?></td>
        <td>
          <a href="<?php echo $item->parent()->abs_url() ?>">
            <?php echo $item->parent()->abs_url() ?>
          </a>
        </td>
      </tr>
    </table>
  </body>
</html>
