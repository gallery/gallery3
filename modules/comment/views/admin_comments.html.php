<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var set_state_url =
    "<?= url::site("admin/comments/set_state/__ID__/__STATE__?csrf=" . access::csrf_token()) ?>";
  function set_state(state, id) {
    $.get(set_state_url.replace("__STATE__", state).replace("__ID__", id),
          {},
          function() {
            $("#gComment-" + id).slideUp();
            update_menu();
          });
  }

  var delete_url =
    "<?= url::site("admin/comments/delete/__ID__?csrf=" . access::csrf_token()) ?>";

  function del(id) {
    $.get(delete_url.replace("__ID__", id),
          {},
          function() {
            $("#gComment-" + id).slideUp();
            update_menu();
          });
  }

  function update_menu() {
    $.get("<?= url::site("admin/comments/menu_labels") ?>", {},
          function(data) {
            for (var i = 0; i < data.length; i++) {
              $("#gAdminCommentsMenu li:eq(" + i + ") a").html(data[i]);
            }
          },
          "json");
  }
</script>

<div id="gAdminComments">
  <h1> <?= t("Manage Comments") ?> </h1>

  <!-- @todo: Highlight active menu option -->
  <div id="gAdminCommentsMenu">
    <?= $menu ?>
  </div>

  <!-- @todo: Remove after setting active option? -->
  <h2>
    <?= $title ?>
  </h2>

  <? if ($queue == "spam"): ?>
  <div>
    <? if ($spam_caught > 0): ?>
    <p>
      <?= t2("Gallery has caught %count spam for you since you installed spam filtering.",
             "Gallery has caught %count spam for you since you installed spam filtering.",
             $spam_caught) ?>
    </p>
    <? endif ?>
    <p>
      <? if ($spam->count()): ?>
      <?= t2("There is currently one comment in your spam queue.  You can delete it with a single click, but there is no undo operation so you may want to check the message first to make sure that it really is spam.",
             "There are currently %count comments in your spam queue.  You can delete them all with a single click, but there is no undo operation so you may want to check the messages first to make sure that they really are spam.  All spam messages will be deleted after 7 days automatically.",
             $spam->count()) ?>
    </p>
    <p>
      <a href="<?= url::site("admin/comments/delete_all_spam?csrf=" . access::csrf_token()) ?>">
        <?= t("Delete all spam") ?>
      </a>
      <? else: ?>
      <?= t("Your spam queue is empty!") ?>
      <? endif ?>
    </p>
  </div>
  <? endif ?>

  <? if ($queue == "deleted"): ?>
  <div>
    <p>
      <?= t("These are messages that have been recently deleted.  They will be permanently erased automatically after 7 days.") ?>
    </p>
  </div>
  <? endif ?>

  <table id="gAdminCommentsList">
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
    <? foreach ($comments as $comment): ?>
    <tr id="gComment-<?= $comment->id ?>">
      <td>
        <a href="#">
          <img src="<?= $comment->author()->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
               class="gAvatar"
               alt="<?= $comment->author_name() ?>"
               width="40"
               height="40" />
        </a><br/>
        <a href="mailto:<?= $comment->author_email() ?>"
           title="<?= $comment->author_email() ?>"> <?= $comment->author_name() ?> </a>
      </td>
      <td>
        <div class="right">
          <? $item = $comment->item(); ?>
          <a href="<?= $item->url() ?>">
            <img src="<?= $item->thumb_url() ?>"
                 alt="<?= $item->title ?>"
                 <?= photo::img_dimensions($item->thumb_width, $item->thumb_height, 75) ?>
              />
          </a><br/>
          <a href="<?= $item->url() ?>"> <?= $item->title ?> </a>
        </div>
        <p><?= date("Y-M-d", $comment->created); ?></p>
        <?= $comment->text ?>
      </td>
      <td>
        <ul>
          <? if ($comment->state != "unpublished"): ?>
          <li>
            <a href="javascript:set_state('unpublished',<?=$comment->id?>)">
              <?= t("Unapprove") ?>
            </a>
          </li>
          <? endif ?>

          <? if ($comment->state != "published"): ?>
          <li>
            <a href="javascript:set_state('published',<?=$comment->id?>)">
              <?= t("Approve") ?>
            </a>
          </li>
          <? endif ?>

          <? if ($comment->state != "spam"): ?>
          <li>
            <a href="javascript:set_state('spam',<?=$comment->id?>)">
              <?= t("Spam") ?>
            </a>
          </li>
          <? endif ?>

          <li>
            <a href="javascript:reply(<?=$comment->id?>)">
              <?= t("Reply") ?>
            </a>
          </li>

          <li>
            <a href="javascript:Edit(<?=$comment->id?>)">
              <?= t("Edit") ?>
            </a>
          </li>

          <li>
            <a href="javascript:set_state('deleted',<?=$comment->id?>)">
              <?= t("Delete") ?>
            </a>
          </li>
        </ul>
      </td>
    </tr>
    <? endforeach ?>
  </table>

  <div class="pager">
    <?= $pager ?>
  </div>
</div>
