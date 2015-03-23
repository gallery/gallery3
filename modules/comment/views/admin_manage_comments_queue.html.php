<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="g-block-content">
  <?php if ($state == "spam"): ?>
  <div>
    <?php $spam_caught = module::get_var("comment", "spam_caught") ?>
    <?php if ($spam_caught > 0): ?>
    <p>
      <?php echo t2("Gallery has caught %count spam for you since you installed spam filtering.",
             "Gallery has caught %count spam for you since you installed spam filtering.",
             $spam_caught) ?>
    </p>
    <?php endif ?>
    <p>
      <?php if ($counts->spam): ?>
      <?php echo t2("There is currently one comment in your spam queue.  You can delete it with a single click, but there is no undo operation so you may want to check the message first to make sure that it really is spam.",
             "There are currently %count comments in your spam queue.  You can delete them all with a single click, but there is no undo operation so you may want to check the messages first to make sure that they really are spam.  All spam messages will be deleted after 7 days automatically.",
             $counts->spam) ?>
    </p>
    <p>
      <a id="g-delete-all-spam"
         href="<?php echo url::site("admin/manage_comments/delete_all_spam?csrf=$csrf") ?>">
        <?php echo t("Delete all spam") ?>
      </a>
      <?php else: ?>
      <?php echo t("Your spam queue is empty!") ?>
      <?php endif ?>
    </p>
  </div>
  <?php endif ?>

  <?php if ($state == "deleted"): ?>
  <div>
    <p>
      <?php echo t("These are messages that have been recently deleted.  They will be permanently erased automatically after 7 days.") ?>
    </p>
  </div>
  <?php endif ?>

  <div class="g-paginator">
    <?php echo $theme->paginator() ?>
  </div>
  <table id="g-admin-comments-list">
    <tr>
      <th>
        <?php echo t("Author") ?>
      </th>
      <th>
        <?php echo t("Comment") ?>
      </th>
      <th>
        <?php echo t("Actions") ?>
      </th>
    </tr>
    <?php foreach ($comments as $comment): ?>
    <tr id="g-comment-<?php echo $comment->id ?>" class="<?php echo text::alternate("g-odd", "g-even") ?>">
      <td>
        <a href="#">
          <img src="<?php echo $comment->author()->avatar_url(40, $fallback_avatar_url) ?>"
               class="g-avatar"
               alt="<?php echo html::clean_attribute($comment->author_name()) ?>"
               width="40"
               height="40" />
        </a>
        <p>
          <a href="mailto:<?php echo html::clean_attribute($comment->author_email()) ?>"
             title="<?php echo html::clean_attribute($comment->author_email()) ?>">
            <?php echo html::clean($comment->author_name()) ?>
          </a>
        </p>
      </td>
      <td>
        <div class="g-right">
          <?php $item = $comment->item() ?>
          <div class="g-item g-photo">
            <a href="<?php echo $item->url() ?>">
              <?php if ($item->has_thumb()): ?>
              <img src="<?php echo $item->thumb_url() ?>"
                 alt="<?php echo html::purify($item->title)->for_html_attr() ?>"
                 <?php echo photo::img_dimensions($item->thumb_width, $item->thumb_height, 75) ?>
              />
              <?php else: ?>
              <?php echo t("No thumbnail") ?>
              <?php endif ?>
            </a>
          </div>
        </div>
        <p><?php echo gallery::date($comment->created) ?></p>
           <?php echo nl2br(html::purify($comment->text)) ?>
      </td>
      <td>
        <ul class="g-buttonset-vertical">
        <?php if ($comment->state != "unpublished" && $comment->state != "deleted"): ?>
          <li>
            <a href="javascript:set_state('unpublished',<?php echo $comment->id?>)"
                class="g-button ui-state-default ui-icon-left">
              <span class="ui-icon ui-icon-check"></span>
              <?php echo t("Unapprove") ?>
            </a>
          </li>
        <?php endif ?>
        <?php if ($comment->state != "published"): ?>
          <li>
            <a href="javascript:set_state('published',<?php echo $comment->id?>)"
                class="g-button ui-state-default ui-icon-left">
              <span class="ui-icon ui-icon-check"></span>
              <?php if ($state == "deleted"): ?>
              <?php echo t("Undelete") ?>
              <?php else: ?>
              <?php echo t("Approve") ?>
              <?php endif ?>
            </a>
          </li>
        <?php endif ?>
        <?php if ($comment->state != "spam"): ?>
          <li>
            <a href="javascript:set_state('spam',<?php echo $comment->id?>)"
                class="g-button ui-state-default ui-icon-left">
              <span class="ui-icon ui-icon-cancel"></span>
              <?php echo t("Spam") ?>
            </a>
          </li>
        <?php endif ?>
          <!--
          <li>
            <a href="javascript:reply(<?php echo $comment->id?>)"
                class="g-button ui-state-default ui-icon-left">
              <span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>
              <?php echo t("Reply") ?>
            </a>
          </li>
          <li>
            <a href="javascript:Edit(<?php echo $comment->id?>)"
                class="g-button ui-state-default ui-icon-left">
              <span class="ui-icon ui-icon-pencil"></span>
              <?php echo t("Edit") ?>
            </a>
          </li>
          -->
        <?php if ($comment->state != "deleted"): ?>
          <li>
            <a href="javascript:set_state('deleted',<?php echo $comment->id?>)"
                class="g-button ui-state-default ui-icon-left">
              <span class="ui-icon ui-icon-trash"></span>
              <?php echo t("Delete") ?>
            </a>
          </li>
        <?php endif ?>
        </ul>
      </td>
    </tr>
    <?php endforeach ?>
  </table>

  <div class="g-paginator">
    <?php echo $theme->paginator() ?>
  </div>
</div>
