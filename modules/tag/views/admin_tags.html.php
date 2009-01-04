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
      <a href="<?= url::site("admin/tags/delete/$tag->id?csrf=" . access::csrf_token()) ?>">
        <?= _("delete") ?>
      </a>
    </td>
  </tr>
  <? endforeach ?>
</table>
