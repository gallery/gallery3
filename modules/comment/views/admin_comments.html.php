<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gAdminComments">
  <h1> <?= _("Comments") ?> </h1>

  <div id="gAdminCommentsMenu">
    <?= $menu ?>
  </div>

  <div id="gAdminCommentsList">
    <table>
      <tr>
        <th>
          <?= _("Comment") ?>
        </th>
        <th>
          <?= _("Date") ?>
        </th>
        <th>
          <?= _("Actions") ?>
        </th>
      </tr>
      <? foreach ($comments as $comment): ?>
      <tr>
        <td>
          <div>
            <b> <?= $comment->author ?> </b>
          </div>
          <div>
            <b> <?= $comment->url ?> </b> | <b> <?= $comment->email ?> </b>
          </div>
          <div>
            <?= $comment->text ?>
          </div>
          <div>
            <? $item = $comment->item(); ?>
            <img src="<?= $item->thumb_url() ?>"
                 alt="<?= $item->title ?>"
                 <?= photo::img_dimensions($item->thumb_width, $item->thumb_height, 75) ?>
            />
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
              <a href="<?= url::site("admin/comments/mark/unpublished/$comment->id?csrf=" . access::csrf_token()) ?>">
                <?= _("Unapprove") ?>
              </a>
            </li>
            <? endif ?>

            <? if ($comment->state != "published"): ?>
            <li>
              <a href="<?= url::site("admin/comments/mark/published/$comment->id?csrf=" . access::csrf_token()) ?>">
                <?= _("Approve") ?>
              </a>
            </li>
            <? endif ?>

            <? if ($comment->state != "spam"): ?>
            <li>
              <a href="<?= url::site("admin/comments/mark/spam/$comment->id?csrf=" . access::csrf_token()) ?>">
                <?= _("Spam") ?>
              </a>
            </li>
            <? endif ?>

            <? if ($comment->state != "spam"): ?>
            <li>
              <a href="<?= url::site("admin/comments/mark/spam/$comment->id?csrf=" . access::csrf_token()) ?>">
                <?= _("Delete") ?>
              </a>
            </li>
            <? endif ?>
          </ul>
        </td>
      </tr>
      <? endforeach ?>
    </table>
  </div>
</div>
