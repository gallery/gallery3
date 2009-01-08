<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var set_state_url =
    "<?= url::site("admin/comments/set_state/__ID__/__STATE__?csrf=" . access::csrf_token()) ?>";
  function set_state(state, id) {
    $.get(set_state_url.replace("__STATE__", state).replace("__ID__", id));
    $("#gComment-" + id).slideUp();
  }

  var delete_url =
    "<?= url::site("admin/comments/delete/__ID__?csrf=" . access::csrf_token()) ?>";
  function delete(id) {
    $.get(delete_url.replace("__ID__", id));
    $("#gComment-" + id).slideUp();
  }
</script>

<div id="gAdminComments">
  <h1> <?= _("Manage Comments") ?> </h1>

  <div id="gAdminCommentsMenu">
    <?= $menu ?>
  </div>

  <!-- @todo: fix this with CSS -->
  <div style="clear: both"></div>

  <h2>
    <?= $title ?>
  </h2>


  <? if ($queue == "spam"): ?>
  <div>
    <p>
      <? printf(_("Gallery has caught %d spam for you since you installed spam filtering."), $spam_caught) ?>
    </p>
    <p>
      <? if ($spam->count()): ?>
      <? printf(_("There are currently %d comments in your spam queue.  You can delete them all with a single click, but there is no undo operation so you may want to check the messages first to make sure that they really are spam."), $spam->count()) ?>
    </p>
    <p>
      <a href="<?= url::site("admin/comments/delete_all_spam?csrf=" . access::csrf_token()) ?>">
        <?= _("Delete all spam") ?>
      </a>
      <? else: ?>
      <?= _("Your spam queue is empty!") ?>
      <? endif ?>
    </p>
  </div>
  <? endif ?>

  <div class="pager">
    <?= $pager ?>
  </div>

  <div id="gAdminCommentsList">
    <table>
      <tr>
        <th>
          <?= _("Comment") ?>
        </th>
        <th style="width: 100px">
          <?= _("Date") ?>
        </th>
        <th>
          <?= _("Actions") ?>
        </th>
      </tr>
      <? foreach ($comments as $comment): ?>
      <tr id="gComment-<?= $comment->id ?>">
        <td>
          <div>
            <img src="<?= $theme->url("images/avatar.jpg") ?>"/>
            <b> <?= $comment->author ?> </b>
          </div>
          <ul>
            <li> <?= $comment->url ?> </li>
            <li> <?= $comment->email ?> </li>
          </ul>
          <div>
            <?= $comment->text ?>
          </div>
          <div>
            <? $item = $comment->item(); ?>
            <a href="<?= $item->url() ?>">
            <img src="<?= $item->thumb_url() ?>"
                 alt="<?= $item->title ?>"
                 <?= photo::img_dimensions($item->thumb_width, $item->thumb_height, 75) ?>
            />
            </a>
            <?= sprintf(_("Comment left on <a href=\"%s\">%s</a>"), $item->url(), $item->title) ?>
          </div>
        </td>
        <td>
          <?= date("Y-M-d", $comment->created); ?>
        </td>
        <td>
          <ul>
            <? if ($comment->state != "unpublished"): ?>
            <li>
              <a href="javascript:set_state('unpublished',<?=$comment->id?>)">
                <?= _("Unapprove") ?>
              </a>
            </li>
            <? endif ?>

            <? if ($comment->state != "published"): ?>
            <li>
              <a href="javascript:set_state('published',<?=$comment->id?>)">
                <?= _("Approve") ?>
              </a>
            </li>
            <? endif ?>

            <? if ($comment->state != "spam"): ?>
            <li>
              <a href="javascript:set_state('spam',<?=$comment->id?>)">
                <?= _("Spam") ?>
              </a>
            </li>
            <? endif ?>

            <li>
              <a href="javascript:delete(<?=$comment->id?>)">
                <?= _("Delete") ?>
              </a>
            </li>
          </ul>
        </td>
      </tr>
      <? endforeach ?>
    </table>
  </div>
</div>
