<? defined("SYSPATH") or die("No direct script access."); ?>
<form id="gCommentAdd" class="gExpandedForm">
  <fieldset>
    <legend>Add comment</legend>
    <div class="row">
      <label for="gCommentAuthor">Your Name</label>
      <input type="text" name="author" id="gCommentAuthor" class="text" />
    </div>
    <div class="row">
      <label for="gCommentEmail">Your Email (not displayed)</label>
      <input type="text" name="email" id="gCommentEmail" class="text" />
    </div>
    <div class="row">
      <label for="gCommentText">Comment</label>
      <textarea name="text" id="gCommentText"></textarea>
    </div>
    <input type="hidden" id="gItemId" name="item_id" value="<?= $item_id ?>" />
    <input type="submit" id="gCommentSubmit" value="Add" />
  </fieldset>
</form>

