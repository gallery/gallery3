<?php defined("SYSPATH") or die("No direct script access.") ?>
<h1> <?= _("Tags") ?> </h1>

<div id="gTagSearch">
  <form method="get" action="<?= url::site("admin/tags") ?>">
    <fieldset>
      <legend> <?= _("Search Tags") ?> </legend>
      <input name="filter" value="<?= $filter ?>"/>
      <input type="submit" value="<?= _("Search Tags") ?>"/>
    </fieldset>
  </form>
</div>

<table>
  <tr>
    <th> <?= _("Tag") ?> </th>
    <th> <?= _("Photos") ?> </th>
    <th> <?= _("Actions") ?> </th>
  </tr>
  <? foreach ($tags as $tag): ?>
  <tr>
    <td> <?= $tag->name ?> </td>
    <td> <?= $tag->count ?> </td>
    <td>
      <ul>
        <li>
          <a href="<?= url::site("admin/tags/form_delete/$tag->id") ?>" class="gDialogLink"
            title="<?= sprintf(_("Delete tag %s"), $tag->name) ?>">
            <?= _("delete") ?>
          </a>
        </li>
        <li>
          <a href="<?= url::site("admin/tags/form_rename/$tag->id") ?>" class="gDialogLink"
            title="<?= sprintf(_("Rename tag %s"), $tag->name) ?>">
            <?= _("rename") ?>
          </a>
        </li>
      </ul>
    </td>
  </tr>
  <? endforeach ?>
</table>
