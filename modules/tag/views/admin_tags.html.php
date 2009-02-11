<?php defined("SYSPATH") or die("No direct script access.") ?>
<style>
  #gTagAdmin td {
    border: 0;
  }
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
    margin: 0 .2em 0 0;
  }
  #gRenameTagForm input[type="submit"] {
    height: 25px;
  }
  #gRenameTagForm a, #gRenameTagForm span {
    display: block;
    float: left;
    padding: .2em .2em 0 .1em;
  }
</style>
<div class="gBlock">
  <h2>
    <?= t("Tag Admin") ?>
  </h2>

  <? $tags_per_column = $tags->count()/5 ?>
  <? $column_tag_count = 0 ?>

  <table id="gTagAdmin" class="gBlockContent">
    <caption class="understate"><?= t("There are ".$tags->count()." tags") ?></caption>
    <tr>
      <td>
        <? foreach ($tags as $i => $tag): ?>
          <? $current_letter = strtoupper(substr($tag->name, 0, 1)) ?>

          <? if ($i == 0): /* first letter */ ?>
            <strong><?= $current_letter ?></strong>
            <ul>
          <? elseif ($last_letter != $current_letter): /* new letter */ ?>
            <? if ($column_tag_count > $tags_per_column): /* new column */ ?>
            	</td>
              <td>
              <? $column_tag_count = 0 ?>
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
    
          <? $column_tag_count++ ?>
          <? $last_letter = $current_letter ?>
        <? endforeach /* $tags */ ?>
        </ul>
      </td>
    </tr>
  </table>
</div>