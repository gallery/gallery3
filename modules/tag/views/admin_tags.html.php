<?php defined("SYSPATH") or die("No direct script access.") ?>
<style>
  #gTagAdmin ul {
    padding-bottom: .3em;
  }
  #gTagAdmin li {
    padding: .1em 0 .2em .3em;
  }
  #gTagAdmin .gColumn {
    float: left;
    width: 200px;
  }
  .gEditable {
    padding: .1em .3em .2em .3em;
  }
  .gEditable:hover {
    background-color: #ffc;
    cursor: text;
  }
  #gRenameTagForm input {
    padding: 0 .2em 0 .2em;
    clear: none;
    float: left;
  }
  #gRenameTagForm input[type="submit"] {
    height: 25px;
  }
  #gRenameTagForm a, #gEditTagForm span {
    display: block;
    float: left;
    padding: .2em 0 0 .3em;
  }
</style>
<div class="gBlock">
  <h2>
    <?= t("Tag Admin") ?>
  </h2>

  <? $tags_per_column = $tags->count()/5 ?>
  <? $column_tag_number = 0 ?>

  <div id="gTagAdmin" class="gBlockContent">
    <div class="gColumn">
      <? foreach ($tags as $i => $tag): ?>
      <? $current_letter = strtoupper(substr($tag->name, 0, 1)) ?>

      <? if ($i == 0): ?>
      <strong><?= $current_letter ?></strong>
      <ul>
        <? elseif ($last_letter != $current_letter): ?>
        <? if ($column_tag_number > $tags_per_column): ?>
    </div>
    <div class="gColumn">
      <? $column_tag_number = 0 ?>
      <? endif ?>
    </ul>
    <strong><?= $current_letter ?></strong>
    <ul>
      <? endif ?>

      <li>
        <span id="gTag-<?= $tag->id ?>" class="gEditable"
              title="<?= t("Click to edit this tag") ?>"><?= $tag->name ?></span>
        <span class="understate">(<?= $tag->count ?>)</span>
        <a href="<?= url::site("admin/tags/form_delete/$tag->id") ?>" class="gDialogLink"
          title="<?= t("Delete this tag") ?>">X</a>
      </li>

      <? $column_tag_number++ ?>
      <? $last_letter = $current_letter ?>
      <? endforeach ?>
    </ul>

    </div>
  </div>

  <table>
    <tr>
      <th> <?= t("Tag") ?> </th>
      <th> <?= t("Photos") ?> </th>
      <th> <?= t("Actions") ?> </th>
    </tr>
    <? foreach ($tags as $i => $tag): ?>
    <tr class="<?= ($i % 2 == 0) ? "gEvenRow" : "gOddRow" ?>">
      <td> <?= $tag->name ?> </td>
      <td> <?= $tag->count ?> </td>
      <td>
        <ul>
          <li>
            <a href="<?= url::site("admin/tags/form_delete/$tag->id") ?>" class="gDialogLink"
              title="<?= t("Delete tag %tag_name", array("tag_name" => $tag->name)) ?>">
              <?= t("delete") ?>
            </a>
          </li>
          <li>
            <a href="<?= url::site("admin/tags/form_rename/$tag->id") ?>" class="gDialogLink"
              title="<?= t("Rename tag %tag_name", array("tag_name" => $tag->name)) ?>">
              <?= t("rename") ?>
            </a>
          </li>
        </ul>
      </td>
    </tr>
    <? endforeach ?>
  </table>

  <div id="gTagSearch">
    <form method="get" action="<?= url::site("admin/tags") ?>">
      <fieldset>
        <legend> <?= t("Search Tags") ?> </legend>
        <input name="filter" value="<?= $filter ?>"/>
        <input type="submit" value="<?= t("Search Tags") ?>"/>
      </fieldset>
    </form>
  </div>
