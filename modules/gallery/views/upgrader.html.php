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
    h1 {
      font-size: 1.4em;
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
    tr.current td.gallery {
      color: #00d;
    }
    tr.upgradeable td {
      font-weight: bold;
    }
    tr.upgradeable td.gallery {
      color: #00d;
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
    div.button a {
      text-decoration: none;
    }
    div.button:hover {
      background: #ccc;
    }
    div#confirmation {
      position: fixed;
      top: 400px;
      left: 325px;
      background: blue;
      z-index: 1000;
      margin: 10px;
      text-align: center;
    }
    div#confirmation div {
      margin: 2px;
      padding: 20px;
      border: 2px solid #999;
      background: white;
    }
    .gray_on_done {
      opacity: <?= $done ? "0.5" : "1" ?>;
    }
    pre {
      display: inline;
      margin: 0px;
      padding: 0px;
    }
  </style>
  <body>
    <div id="outer">
      <img src="<?= url::file("modules/gallery/images/gallery.png") ?>" />
      <div id="inner">
        <? if ($can_upgrade): ?>
        <? if ($done): ?>
        <div id="confirmation">
          <div>
            <h1> <?= t("That's it!") ?> </h1>
            <p>
              <?= t("Your <a href=\"%url\">Gallery</a> is up to date.",
                    array("url" => url::site("albums/1"))) ?>
            </p>
          </div>
        </div>
        <? endif ?>
        <p class="gray_on_done">
          <?= t("Welcome to the Gallery upgrader.  One click and you're done!") ?>
        </p>
        <table>
          <tr class="gray_on_done">
            <th> <?= t("Module name") ?> </th>
            <th> <?= t("Installed version") ?> </th>
            <th> <?= t("Available version") ?> </th>
          </tr>

          <? foreach ($available as $id => $module): ?>
          <? if ($module->active): ?>
          <tr class="<?= $module->version == $module->code_version ? "current" : "upgradeable" ?>" >
            <td class="name <?= $id ?>">
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

        <div class="button gray_on_done">
          <a href="<?= url::site("upgrader/upgrade") ?>">
            <?= t("Upgrade all") ?>
          </a>
        </div>

        <? if (@$inactive): ?>
        <p class="gray_on_done">
          <?= t("The following modules are inactive and don't require an upgrade.") ?>
        </p>
        <ul class="gray_on_done">
          <? foreach ($available as $module): ?>
          <? if (!$module->active): ?>
          <li>
            <?= $module->name ?>
          </li>
          <? endif ?>
          <? endforeach ?>
        </p>
        <? endif ?>
        <? else: // can_upgrade ?>
        <h1> <?= t("Who are you?") ?> </h1>
        <p>
          <?= t("You're not logged in as an administrator, so we have to verify you to make sure it's ok for you to do an upgrade.  To prove you can run an upgrade, create a file called %name in your <b>gallery3/var/tmp</b> directory.", array("name" => "<br/><b>$upgrade_token</b>")) ?>
        </p>
        <a href="<?= url::site("upgrader?") ?>"><?= t("Ok, I've done that") ?></a>
        <? endif // can_upgrade ?>
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
