<?php defined("SYSPATH") or die("No direct script access.") ?>
<li class="<?= $breadcrumb->class ?>">
  <? if ($breadcrumb->class != "g-active"): ?>
    <a href="<?= $breadcrumb->url ?>">
  <? endif ?>
  <?= html::purify(text::limit_chars($breadcrumb->title, module::get_var("gallery", "visible_title_length"))) ?>
  <? if ($breadcrumb->class != "g-active"): ?>
    </a>
  <? endif ?>
</li>
