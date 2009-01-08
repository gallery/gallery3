<?php defined("SYSPATH") or die("No direct script access.") ?>
<h1> <?= t("Tags") ?> </h1>

<div id="gTagSearch">
  <form method="get" action="<?= url::site("admin/tags") ?>">
    <fieldset>
      <legend> <?= t("Search Tags") ?> </legend>
      <input name="filter" value="<?= $filter ?>"/>
      <input type="submit" value="<?= t("Search Tags") ?>"/>
    </fieldset>
  </form>
</div>

<table>
  <tr>
    <th> <?= t("Tag") ?> </th>
    <th> <?= t("Photos") ?> </th>
    <th> <?= t("Actions") ?> </th>
  </tr>
  <? foreach ($tags as $tag): ?>
  <tr>
    <td> <?= $tag->name ?> </td>
    <td> <?= $tag->count ?> </td>
    <td>
      <ul>
        <li>
          <a href="<?= url::site("admin/tags/form_delete/$tag->id") ?>" class="gDialogLink"
            title="<?= t("Delete tag {{tag_name}}", array("tag_name" => $tag->name)) ?>">
            <?= t("delete") ?>
          </a>
        </li>
        <li>
          <a href="<?= url::site("admin/tags/form_rename/$tag->id") ?>" class="gDialogLink"
            title="<?= t("Rename tag {{tag_name}}", array("tag_name" => $tag->name)) ?>">
            <?= t("rename") ?>
          </a>
        </li>
      </ul>
    </td>
  </tr>
  <? endforeach ?>
</table>
