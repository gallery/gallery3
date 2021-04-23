<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="g-block-content">
  <?php if ($state == "spam"): ?>
  <div>
    <?php $spam_caught = module::get_var("comment", "spam_caught") ?>
    <?php if ($spam_caught > 0): ?>
    <p>
      <?= t2("Gallery has caught %count spam for you since you installed spam filtering.",
             "Gallery has caught %count spam for you since you installed spam filtering.",
             $spam_caught) ?>
    </p>
    <?php endif ?>
    <p>
      <?php if ($counts->spam): ?>
      <?= t2("There is currently one comment in your spam queue.  You can delete it with a single click, but there is no undo operation so you may want to check the message first to make sure that it really is spam.",
             "There are currently %count comments in your spam queue.  You can delete them all with a single click, but there is no undo operation so you may want to check the messages first to make sure that they really are spam.  All spam messages will be deleted after 7 days automatically.",
             $counts->spam) ?>
    </p>
    <p>
      <a id="g-delete-all-spam"
         href="<?= url::site("admin/manage_comments/delete_all_spam?csrf=$csrf") ?>">
        <?= t("Delete all spam") ?>
      </a>
      <?php else: ?>
      <?= t("Your spam queue is empty!") ?>
      <?php endif ?>
    </p>
  </div>
  <?php endif ?>

  <?php if ($state == "deleted"): ?>
  <div>
    <p>
      <?= t("These are messages that have been recently deleted.  They will be permanently erased automatically after 7 days.") ?>
    </p>
  </div>
  <?php endif ?>

  <div class="g-paginator">
    <?= $theme->paginator() ?>
  </div>
  <table id="g-admin-comments-list">
    <tr>
      <th>
        <?= t("Author") ?>
      </th>
      <th>
        <?= t("Comment") ?>
      </th>
      <th>
        <?= t("Actions") ?>
      </th>
    </tr>
    <?php foreach ($comments as $comment): ?>
    <tr id="g-comment-<?= $comment->id ?>" class="<?= text::alternate("g-odd", "g-even") ?>">
      <td>
        <a href="#">
          <img src="<?= $comment->author()->avatar_url(40, $fallback_avatar_url) ?>"
               class="g-avatar"
               alt="<?= html::clean_attribute($comment->author_name()) ?>"
               width="40"
               height="40" />
        </a>
        <p>
          <a href="mailto:<?= html::clean_attribute($comment->author_email()) ?>"
             title="<?= html::clean_attribute($comment->author_email()) ?>">
            <?= html::clean($comment->author_name()) ?>
          </a>
        </p>
      </td>
      <td>
        <div class="g-right">
          <?php $item = $comment->item() ?>
          <div class="g-item g-photo">
            <a href="<?= $item->url() ?>">
              <?php if ($item->has_thumb()): ?>
              <img src="<?= $item->thumb_url() ?>"
                 alt="<?= html::purify($item->title)->for_html_attr() ?>"
                 <?= photo::img_dimensions($item->thumb_width, $item->thumb_height, 75) ?>
              />
              <?php else: ?>
              <?= t("No thumbnail") ?>
              <?php endif ?>
            </a>
          </div>
        </div>
        <p><?= gallery::date($comment->created) ?></p>
           <?= nl2br(html::purify($comment->text)) ?>
      </td>
      <td>
        <ul class="g-buttonset-vertical">
        <?php if ($comment->state != "unpublished" && $comment->state != "deleted"): ?>
          <li>
            <a href="javascript:set_state('unpublished',<?=$comment->id?>)"
                class="g-button ui-state-default ui-icon-left">
              <span class="ui-icon ui-icon-check"></span>
              <?= t("Unapprove") ?>
            </a>
          </li>
        <?php endif ?>
        <?php if ($comment->state != "published"): ?>
          <li>
            <a href="javascript:set_state('published',<?=$comment->id?>)"
                class="g-button ui-state-default ui-icon-left">
              <span class="ui-icon ui-icon-check"></span>
              <?php if ($state == "deleted"): ?>
              <?= t("Undelete") ?>
              <?php else: ?>
              <?= t("Approve") ?>
              <?php endif ?>
            </a>
          </li>
        <?php endif ?>
        <?php if ($comment->state != "spam"): ?>
          <li>
            <a href="javascript:set_state('spam',<?=$comment->id?>)"
                class="g-button ui-state-default ui-icon-left">
              <span class="ui-icon ui-icon-cancel"></span>
              <?= t("Spam") ?>
            </a>
          </li>
        <?php endif ?>
          <!--
          <li>
            <a href="javascript:reply(<?=$comment->id?>)"
                class="g-button ui-state-default ui-icon-left">
              <span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>
              <?= t("Reply") ?>
            </a>
          </li>
          <li>
            <a href="javascript:Edit(<?=$comment->id?>)"
                class="g-button ui-state-default ui-icon-left">
              <span class="ui-icon ui-icon-pencil"></span>
              <?= t("Edit") ?>
            </a>
          </li>
          -->
        <?php if ($comment->state != "deleted"): ?>
          <li>
            <a href="javascript:set_state('deleted',<?=$comment->id?>)"
                class="g-button ui-state-default ui-icon-left">
              <span class="ui-icon ui-icon-trash"></span>
              <?= t("Delete") ?>
            </a>
          </li>
        <?php endif ?>
        </ul>
      </td>
    </tr>
    <?php endforeach ?>
  </table>

  <div class="g-paginator">
    <?= $theme->paginator() ?>
  </div>
</div>
