<?php defined("SYSPATH") or die("No direct script access.") ?>
<fieldset>
  <legend> <?php echo  t('Edit Permissions') ?> </legend>
  <table>
    <tr>
      <th> </th>
      <?php foreach ($groups as $group): ?>
      <th> <?php echo  html::clean($group->name) ?> </th>
      <?php endforeach ?>
    </tr>

    <?php foreach ($permissions as $permission): ?>
    <tr>
  <td> <?php echo  t($permission->display_name) ?>
 </td>
      <?php foreach ($groups as $group): ?>
        <?php $intent = access::group_intent($group, $permission->name, $item) ?>
        <?php $allowed = access::group_can($group, $permission->name, $item) ?>
        <?php $lock = access::locked_by($group, $permission->name, $item) ?>

        <?php if ($lock): ?>
          <td class="g-denied">
            <img src="<?php echo  url::file(gallery::find_file("images", "ico-denied.png")) ?>"
                 title="<?php echo  t('denied and locked through parent album')->for_html_attr() ?>"
                 alt="<?php echo  t('denied icon')->for_html_attr() ?>" />
            <a href="javascript:show(<?php echo  $lock->id ?>)" title="<?php echo  t('click to go to parent album')->for_html_attr() ?>">
              <img src="<?php echo  url::file(gallery::find_file("images", "ico-lock.png")) ?>" alt="<?php echo  t('locked icon')->for_html_attr() ?>" />
            </a>
          </td>
        <?php else: ?>
          <?php if ($intent === access::INHERIT): ?>
            <?php if ($allowed): ?>
              <td class="g-allowed">
                <a href="javascript:set('allow',<?php echo  $group->id ?>,<?php echo  $permission->id ?>,<?php echo  $item->id ?>)" title="<?php echo  t('allowed through parent album, click to allow explicitly')->for_html_attr() ?>">
                  <img src="<?php echo  url::file(gallery::find_file("images", "ico-success-passive.png")) ?>" alt="<?php echo  t('passive allowed icon')->for_html_attr() ?>" />
                </a>
                <a href="javascript:set('deny',<?php echo  $group->id ?>,<?php echo  $permission->id ?>,<?php echo  $item->id ?>)"
                  title="<?php echo  t('click to deny')->for_html_attr() ?>">
                  <img src="<?php echo  url::file(gallery::find_file("images", "ico-denied-inactive.png")) ?>" alt="<?php echo  t('inactive denied icon')->for_html_attr() ?>" />
                </a>
              </td>
            <?php else: ?>
              <td class="g-denied">
                <a href="javascript:set('allow',<?php echo  $group->id ?>,<?php echo  $permission->id ?>,<?php echo  $item->id ?>)"
                  title="<?php echo  t('click to allow')->for_html_attr() ?>">
                  <img src="<?php echo  url::file(gallery::find_file("images", "ico-success-inactive.png")) ?>" alt="<?php echo  t('inactive allowed icon')->for_html_attr() ?>" />
                </a>
                <a href="javascript:set('deny',<?php echo  $group->id ?>,<?php echo  $permission->id ?>,<?php echo  $item->id ?>)"
                  title="<?php echo  t('denied through parent album, click to deny explicitly')->for_html_attr() ?>">
                  <img src="<?php echo  url::file(gallery::find_file("images", "ico-denied-passive.png")) ?>" alt="<?php echo  t('passive denied icon')->for_html_attr() ?>" />
                </a>
              </td>
            <?php endif ?>

          <?php elseif ($intent === access::DENY): ?>
            <td class="g-denied">
              <a href="javascript:set('allow',<?php echo  $group->id ?>,<?php echo  $permission->id ?>,<?php echo  $item->id ?>)"
                title="<?php echo  t('click to allow')->for_html_attr() ?>">
                <img src="<?php echo  url::file(gallery::find_file("images", "ico-success-inactive.png")) ?>" alt="<?php echo  t('inactive allowed icon')->for_html_attr() ?>" />
              </a>
              <?php if ($item->id == 1): ?>
                <img src="<?php echo  url::file(gallery::find_file("images", "ico-denied.png")) ?>" alt="<?php echo  t('denied icon')->for_html_attr() ?>" title="<?php echo  t('denied')->for_html_attr() ?>"/>
              <?php else: ?>
                <a href="javascript:set('reset',<?php echo  $group->id ?>,<?php echo  $permission->id ?>,<?php echo  $item->id ?>)"
                  title="<?php echo  t('denied, click to reset')->for_html_attr() ?>">
                  <img src="<?php echo  url::file(gallery::find_file("images", "ico-denied.png")) ?>" alt="<?php echo  t('denied icon')->for_html_attr() ?>" />
                </a>
              <?php endif ?>
            </td>
          <?php elseif ($intent === access::ALLOW): ?>
            <td class="g-allowed">
              <?php if ($item->id == 1): ?>
                <img src="<?php echo  url::file(gallery::find_file("images", "ico-success.png")) ?>" title="<?php echo  t("allowed")->for_html_attr() ?>" alt="<?php echo  t('allowed icon')->for_html_attr() ?>" />
              <?php else: ?>
                <a href="javascript:set('reset',<?php echo  $group->id ?>,<?php echo  $permission->id ?>,<?php echo  $item->id ?>)"
                  title="<?php echo  t('allowed, click to reset')->for_html_attr() ?>">
                  <img src="<?php echo  url::file(gallery::find_file("images", "ico-success.png")) ?>" alt="<?php echo  t('allowed icon')->for_html_attr() ?>" />
                </a>
              <?php endif ?>
              <a href="javascript:set('deny',<?php echo  $group->id ?>,<?php echo  $permission->id ?>,<?php echo  $item->id ?>)"
                title="<?php echo  t('click to deny')->for_html_attr() ?>">
                <img src="<?php echo  url::file(gallery::find_file("images", "ico-denied-inactive.png")) ?>" alt="<?php echo  t('inactive denied icon')->for_html_attr() ?>" />
              </a>
            </td>
          <?php endif ?>
        <?php endif ?>
      </td>
      <?php endforeach ?>
    </tr>
    <?php endforeach ?>
  </table>
</fieldset>
