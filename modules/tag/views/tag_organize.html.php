<?php defined("SYSPATH") or die("No direct script access.") ?>
<span id="gTagCount"><?= t2("There is one tag", "There are %count tags", $tag_count) ?></span>
<span id="gAddTag">
  <a id="gAddTagButton" href="#" class="gButtonLink"><?= t("Click to Add Tag") ?></a>
</span>
<? foreach ($tags as $firstLetter => $tagGroup): ?>
<div class="gTagGroup">
  <strong><?= $firstLetter ?></strong><span class="understate">&nbsp;(<?= $tagGroup["count"] ?>)</span>
  <ul>
    <? foreach ($tagGroup["taglist"] as $taglist): ?>
    <li>
      <span id="gTag-<?= $taglist['id'] ?>" class="gEditable tag-name"><?= $taglist["tag"] ?></span>
      <span class="understate">(<?= $taglist["count"] ?>)</span>

      <!-- a href="<?= url::site("admin/tags/form_delete/{$taglist['id']}") ?>"
               class="gDialogLink delete-link gButtonLink" -->
      <a href="#"
               class="gDialogLink delete-link gButtonLink">
        <span class="ui-icon ui-icon-trash"><?= t("Delete tag") ?></span>
      </a>
     </li>
    <? endforeach ?>
  </ul>
</div>
<? endforeach ?>