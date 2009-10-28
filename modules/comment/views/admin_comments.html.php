<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var set_state_url =
    <?= html::js_string(url::site("admin/comments/set_state/__ID__/__STATE__?csrf=$csrf")) ?>;
  function set_state(state, id) {
    $.get(set_state_url.replace("__STATE__", state).replace("__ID__", id),
          {},
          function() {
            $("#g-comment-" + id).slideUp();
            update_menu();
          });
  }

  var delete_url =
    <?= html::js_string(url::site("admin/comments/delete/__ID__?csrf=$csrf")) ?>;

  function del(id) {
    $.get(delete_url.replace("__ID__", id),
          {},
          function() {
            $("#g-comment-" + id).slideUp();
            update_menu();
          });
  }

  function update_menu() {
    $.get(<?= html::js_string(url::site("admin/comments/menu_labels")) ?>, {},
          function(data) {
            for (var i = 0; i < data.length; i++) {
              $("#g-admin-comments-menu li:eq(" + i + ") a").html(data[i]);
            }
          },
          "json");
  }
</script>

<div id="g-admin-comments" class="g-block">
  <h1> <?= t("Manage comments") ?> </h1>

  <div class="g-block-content">
    <!-- @todo: Highlight active menu option -->
    <div id="g-admin-comments-menu" class="ui-helper-clearfix">
      <?= $menu ?>
    </div>

    <!-- @todo: Remove after setting active option? -->
    <h2>
      <? if ($state == "published"): ?>
      <?= t("Approved comments") ?>
      <? elseif ($state == "unpublished"): ?>
      <?= t("Comments awaiting moderation") ?>
      <? elseif ($state == "spam"): ?>
      <?= t("Spam comments") ?>
      <? elseif ($state == "deleted"): ?>
      <?= t("Recently deleted comments") ?>
      <? endif ?>
    </h2>

    <? if ($state == "spam"): ?>
    <div>
      <? $spam_caught = module::get_var("comment", "spam_caught") ?>
      <? if ($spam_caught > 0): ?>
      <p>
        <?= t2("Gallery has caught %count spam for you since you installed spam filtering.",
               "Gallery has caught %count spam for you since you installed spam filtering.",
               $spam_caught) ?>
      </p>
      <? endif ?>
      <p>
        <? if ($counts->spam): ?>
        <?= t2("There is currently one comment in your spam queue.  You can delete it with a single click, but there is no undo operation so you may want to check the message first to make sure that it really is spam.",
               "There are currently %count comments in your spam queue.  You can delete them all with a single click, but there is no undo operation so you may want to check the messages first to make sure that they really are spam.  All spam messages will be deleted after 7 days automatically.",
               $counts->spam) ?>
      </p>
      <p>
        <a href="<?= url::site("admin/comments/delete_all_spam?csrf=$csrf") ?>">
          <?= t("Delete all spam") ?>
        </a>
        <? else: ?>
        <?= t("Your spam queue is empty!") ?>
        <? endif ?>
      </p>
    </div>
    <? endif ?>

    <? if ($state == "deleted"): ?>
    <div>
      <p>
        <?= t("These are messages that have been recently deleted.  They will be permanently erased automatically after 7 days.") ?>
      </p>
    </div>
    <? endif ?>

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
      <? foreach ($comments as $i => $comment): ?>
      <tr id="g-comment-<?= $comment->id ?>" class="<?= ($i % 2 == 0) ? "g-odd" : "g-even" ?>">
        <td>
          <a href="#">
            <img src="<?= $comment->author()->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
                 class="g-avatar"
                 alt="<?= html::clean_attribute($comment->author_name()) ?>"
                 width="40"
                 height="40" />
          </a>
          <p><a href="mailto:<?= html::clean_attribute($comment->author_email()) ?>"
                title="<?= html::clean_attribute($comment->author_email()) ?>"> <?= html::clean($comment->author_name()) ?> </a></p>
        </td>
        <td>
          <div class="g-right">
            <? $item = $comment->item() ?>
            <div class="g-item g-photo">
              <a href="<?= $item->url() ?>">
                <? if ($item->has_thumb()): ?>
                <img src="<?= $item->thumb_url() ?>"
                   alt="<?= html::purify($item->title)->for_html_attr() ?>"
                   <?= photo::img_dimensions($item->thumb_width, $item->thumb_height, 75) ?>
                />
                <? else: ?>
                <?= t("No thumbnail") ?>
                <? endif ?>
              </a>
            </div>
          </div>
          <p><?= gallery::date($comment->created) ?></p>
             <?= nl2br(html::purify($comment->text)) ?>
        </td>
        <td>
          <ul class="g-buttonset-vertical">
          <? if ($comment->state != "unpublished"): ?>
            <li>
              <a href="javascript:set_state('unpublished',<?=$comment->id?>)"
                  class="g-button ui-state-default ui-icon-left">
                <span class="ui-icon ui-icon-check"></span>
                <?= t("Unapprove") ?>
              </a>
            </li>
          <? endif ?>
          <? if ($comment->state != "published"): ?>
            <li>
              <a href="javascript:set_state('published',<?=$comment->id?>)"
                  class="g-button ui-state-default ui-icon-left">
                <span class="ui-icon ui-icon-check"></span>
                <?= t("Approve") ?>
              </a>
            </li>
          <? endif ?>
          <? if ($comment->state != "spam"): ?>
            <li>
              <a href="javascript:set_state('spam',<?=$comment->id?>)"
                  class="g-button ui-state-default ui-icon-left">
                <span class="ui-icon ui-icon-cancel"></span>
                <?= t("Spam") ?>
              </a>
            </li>
          <? endif ?>
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
            <li>
              <a href="javascript:set_state('deleted',<?=$comment->id?>)"
                  class="g-button ui-state-default ui-icon-left">
                <span class="ui-icon ui-icon-trash"></span>
                <?= t("Delete") ?>
              </a>
            </li>
          </ul>
        </td>
      </tr>
      <? endforeach ?>
    </table>

    <div class="g-pager">
      <?= $pager ?>
    </div>

  </div>
</div>
