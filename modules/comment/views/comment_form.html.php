<? defined("SYSPATH") or die("No direct script access."); ?>
<form id="gCommentAdd">
  <fieldset>
    <legend>Add comment</legend>
    <ul>
      <li>
        <label for="gCommentAuthor"><?= _("Your Name") ?></label>
        <input type="text" name="author" id="gCommentAuthor" />
      </li>
      <li>
        <label for="gCommentEmail"><?= _("Your Email (not displayed)") ?></label>
        <input type="text" name="email" id="gCommentEmail" />
      </li>
      <li>
        <label for="gCommentText"><?= _("Comment") ?></label>
        <textarea name="text" id="gCommentText"></textarea>
      </li>
      <li>
        <input type="hidden" id="gItemId" name="item_id" value="<?= $item_id ?>" />
        <input type="submit" id="gCommentSubmit" value="<?= _("Add") ?>" />
      </li>
    </ul>
  </fieldset>
</form>

