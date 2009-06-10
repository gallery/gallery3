<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= t("Gallery3 Upgrader") ?></title>
  </head>
  <style>
    body {
      background: #eee;
      font-family: Trebuchet MS;
      font-size: 1.1em;
    }
    div#outer {
      width: 650px;
      background: white;
      border: 1px solid #999;
      margin: 0 auto;
        padding: -10px;
    }
    div#inner {
      padding: 0 1em 0 1em;
      margin: 0px;
    }
    div#footer {
      border-top: 1px solid #ccc;
      margin: 1em;
    }
    td.name {
      text-align: left;
      padding-left: 30px;
    }
    td {
      text-align: center;
      border-bottom: 1px solid #eee;
    }
    tr.current td {
      color: #999;
      font-style: italic;
    }
    tr.upgradeable td {
      font-weight: bold;
    }
    table {
      width: 600px;
      margin-bottom: 10px;
    }
    p {
      font-size: .9em;
    }
    ul {
      font-size: .9em;
      list-style: none;
    }
    li {
      display: inline;
    }
    li:before {
      content: "\00BB \0020";
    }
    div.button {
      margin: 0 auto;
      width: 120px;
      text-align: center;
      border: 1px solid #999;
      background: #eee;
    }
    div.button:hover {
      background: #ccc;
    }
  </style>
  <body>
    <div id="outer">
      <img src="<?= url::file("modules/gallery/images/gallery.png") ?>" />
      <div id="inner">
        <p>
          <?= t("Welcome to the Gallery upgrader.  One click and you're done!") ?>
        </p>
        <table>
          <tr>
            <th> <?= t("Module name") ?> </th>
            <th> <?= t("Installed version") ?> </th>
            <th> <?= t("Available version") ?> </th>
          </tr>

          <? foreach (module::available() as $module): ?>
          <? if ($module->active): ?>
          <tr class="<?= $module->version == $module->code_version ? "current" : "upgradeable" ?>" >
            <td class="name">
              <?= $module->name ?>
            </td>
            <td>
              <?= $module->version ?>
            </td>
            <td>
              <?= $module->code_version ?>
            </td>
          </tr>
          <? else: ?>
          <? @$inactive++ ?>
          <? endif ?>
          <? endforeach ?>
        </table>

        <div class="button">
          <a href="<?= url::site("upgrader/upgrade") ?>">
            <?= t("Upgrade all") ?>
          </a>
        </div>

        <? if (@$inactive): ?>
        <p>
          <?= t("The following modules are inactive and don't require an upgrade.") ?>
        </p>
        <ul>
          <? foreach (module::available() as $module): ?>
          <? if (!$module->active): ?>
          <li>
            <?= $module->name ?>
          </li>
          <? endif ?>
          <? endforeach ?>
        </p>
        <? endif ?>
      </div>
      <div id="footer">
        <p>
          <i>
            <?= t("Did something go wrong? Try the <a href=\"%faq_url\">FAQ</a> or ask in the <a href=\"%forums_url\">Gallery forums</a>.</i>",
                array("faq_url" => "http://codex.gallery2.org/Gallery3:FAQ",
                      "forums_url" => "http://gallery.menalto.com/forum")) ?>
          </i>
        </p>
      </div>
    </div>
  </body>
</html>
