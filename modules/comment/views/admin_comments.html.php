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
  function del(id) {
    $.get(delete_url.replace("__ID__", id));
    $("#gComment-" + id).slideUp();
  }
</script>

<div id="gAdminComments">
  <h1> <?= t("Manage Comments") ?> </h1>

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
      <?= t(array("one" => "Gallery has caught {{count}} spam for you since you installed spam filtering.",
                  "other" => "Gallery has caught {{count}} spam for you since you installed spam filtering."),
            array("count" => $spam_caught)) ?>
    </p>
    <p>
      <? if ($spam->count()): ?>
      <?= t(array("one" => "There is currently one comment in your spam queue.  You can delete it with a single click, but there is no undo operation so you may want to check the message first to make sure that it really is spam.",
                  "other" => "There are currently {{count}} comments in your spam queue.  You can delete them all with a single click, but there is no undo operation so you may want to check the messages first to make sure that they really are spam."),
            array("count" => $spam->count())) ?>
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

  <div class="pager">
    <?= $pager ?>
  </div>

  <div id="gAdminCommentsList">
    <table>
      <tr>
        <th>
          <?= t("Comment") ?>
        </th>
        <th style="width: 100px">
          <?= t("Date") ?>
        </th>
        <th>
          <?= t("Actions") ?>
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
            <?= t("Comment left on {{item_title}}",
                  array("item_title" => sprintf("<a href=\"%s\">%s</a>", $item->url(), $item->title))) ?>
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
              <a href="javascript:del(<?=$comment->id?>)">
                <?= t("Delete") ?>
              </a>
            </li>
          </ul>
        </td>
      </tr>
      <? endforeach ?>
    </table>
  </div>
</div>
