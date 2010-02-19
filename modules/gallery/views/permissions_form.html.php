<?php defined("SYSPATH") or die("No direct script access.") ?>
<fieldset>
  <legend> <?= t('Edit Permissions') ?> </legend>
  <table>
    <tr>
      <th> </th>
      <? foreach ($groups as $group): ?>
      <th> <?= html::clean($group->name) ?> </th>
      <? endforeach ?>
    </tr>

    <? foreach ($permissions as $permission): ?>
    <tr>
  <td> <?= t($permission->display_name) ?>
 </td>
      <? foreach ($groups as $group): ?>
        <? $intent = access::group_intent($group, $permission->name, $item) ?>
        <? $allowed = access::group_can($group, $permission->name, $item) ?>
        <? $lock = access::locked_by($group, $permission->name, $item) ?>

        <? if ($lock): ?>
          <td class="g-denied">
            <img src="<?= url::file(gallery::find_file("images", "ico-denied.png")) ?>"
                 title="<?= t('denied and locked through parent album')->for_html_attr() ?>"
                 alt="<?= t('denied icon')->for_html_attr() ?>" />
            <a href="javascript:show(<?= $lock->id ?>)" title="<?= t('click to go to parent album')->for_html_attr() ?>">
              <img src="<?= url::file(gallery::find_file("images", "ico-lock.png")) ?>" alt="<?= t('locked icon')->for_html_attr() ?>" />
            </a>
          </td>
        <? else: ?>
          <? if ($intent === access::INHERIT): ?>
            <? if ($allowed): ?>
              <td class="g-allowed">
                <a href="javascript:set('allow',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)" title="<?= t('allowed through parent album, click to allow explicitly')->for_html_attr() ?>">
                  <img src="<?= url::file(gallery::find_file("images", "ico-success-passive.png")) ?>" alt="<?= t('passive allowed icon')->for_html_attr() ?>" />
                </a>
                <a href="javascript:set('deny',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)"
                  title="<?= t('click to deny')->for_html_attr() ?>">
                  <img src="<?= url::file(gallery::find_file("images", "ico-denied-inactive.png")) ?>" alt="<?= t('inactive denied icon')->for_html_attr() ?>" />
                </a>
              </td>
            <? else: ?>
              <td class="g-denied">
                <a href="javascript:set('allow',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)"
                  title="<?= t('click to allow')->for_html_attr() ?>">
                  <img src="<?= url::file(gallery::find_file("images", "ico-success-inactive.png")) ?>" alt="<?= t('inactive allowed icon')->for_html_attr() ?>" />
                </a>
                <a href="javascript:set('deny',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)"
                  title="<?= t('denied through parent album, click to deny explicitly')->for_html_attr() ?>">
                  <img src="<?= url::file(gallery::find_file("images", "ico-denied-passive.png")) ?>" alt="<?= t('passive denied icon')->for_html_attr() ?>" />
                </a>
              </td>
            <? endif ?>

          <? elseif ($intent === access::DENY): ?>
            <td class="g-denied">
              <a href="javascript:set('allow',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)"
                title="<?= t('click to allow')->for_html_attr() ?>">
                <img src="<?= url::file(gallery::find_file("images", "ico-success-inactive.png")) ?>" alt="<?= t('inactive allowed icon')->for_html_attr() ?>" />
              </a>
              <? if ($item->id == 1): ?>
                <img src="<?= url::file(gallery::find_file("images", "ico-denied.png")) ?>" alt="<?= t('denied icon')->for_html_attr() ?>" title="<?= t('denied')->for_html_attr() ?>"/>
              <? else: ?>
                <a href="javascript:set('reset',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)"
                  title="<?= t('denied, click to reset')->for_html_attr() ?>">
                  <img src="<?= url::file(gallery::find_file("images", "ico-denied.png")) ?>" alt="<?= t('denied icon')->for_html_attr() ?>" />
                </a>
              <? endif ?>
            </td>
          <? elseif ($intent === access::ALLOW): ?>
            <td class="g-allowed">
              <? if ($item->id == 1): ?>
                <img src="<?= url::file(gallery::find_file("images", "ico-success.png")) ?>" title="<?= t("allowed")->for_html_attr() ?>" alt="<?= t('allowed icon')->for_html_attr() ?>" />
              <? else: ?>
                <a href="javascript:set('reset',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)"
                  title="<?= t('allowed, click to reset')->for_html_attr() ?>">
                  <img src="<?= url::file(gallery::find_file("images", "ico-success.png")) ?>" alt="<?= t('allowed icon')->for_html_attr() ?>" />
                </a>
              <? endif ?>
              <a href="javascript:set('deny',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)"
                title="<?= t('click to deny')->for_html_attr() ?>">
                <img src="<?= url::file(gallery::find_file("images", "ico-denied-inactive.png")) ?>" alt="<?= t('inactive denied icon')->for_html_attr() ?>" />
              </a>
            </td>
          <? endif ?>
        <? endif ?>
      </td>
      <? endforeach ?>
    </tr>
    <? endforeach ?>
  </table>
</fieldset>
