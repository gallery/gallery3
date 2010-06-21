<?php defined("SYSPATH") or die("No direct script access.") ?>
<? echo '<?xml version="1.0" ?>' ?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/"
   xmlns:atom="http://www.w3.org/2005/Atom"
   xmlns:content="http://purl.org/rss/1.0/modules/content/"
   xmlns:fh="http://purl.org/syndication/history/1.0">
  <channel>
    <generator>gallery3</generator>
    <title><?= html::clean($feed->title) ?></title>
    <link><?= $feed->uri ?></link>
    <description><?= html::clean($feed->description) ?></description>
    <language>en-us</language>
    <atom:link rel="self" href="<?= $feed->uri ?>" type="application/rss+xml" />
    <fh:complete/>
    <? if (!empty($feed->previous_page_uri)): ?>
    <atom:link rel="previous" href="<?= $feed->previous_page_uri ?>" type="application/rss+xml" />
    <? endif ?>
    <? if (!empty($feed->next_page_uri)): ?>
    <atom:link rel="next" href="<?= $feed->next_page_uri ?>" type="application/rss+xml" />
    <? endif ?>
    <pubDate><?= $pub_date ?></pubDate>
    <lastBuildDate><?= $pub_date ?></lastBuildDate>
    <? foreach ($feed->items as $item): ?>
    <item>
      <title><?= html::purify($item->title) ?></title>
      <link><?= url::abs_site("{$item->type}s/{$item->id}") ?></link>
      <guid isPermaLink="true"><?= url::abs_site("{$item->type}s/{$item->id}") ?></guid>
      <pubDate><?= date("D, d M Y H:i:s T", $item->created); ?></pubDate>
      <description><?= html::purify($item->description) ?></description>
      <content:encoded>
        <![CDATA[
          <span><?= html::purify($item->description) ?></span>
          <p>
          <? if ($item->type == "photo"): ?>
            <img alt="" src="<?= $item->resize_url(true) ?>"
                 title="<?= html::purify($item->title)->for_html_attr() ?>"
                 height="<?= $item->resize_height ?>" width="<?= $item->resize_width ?>" /><br />
          <? else: ?>
            <a href="<?= url::abs_site("{$item->type}s/{$item->id}") ?>">
            <img alt="" src="<?= $item->thumb_url(true) ?>"
                 title="<?= html::purify($item->title)->for_html_attr() ?>"
                 height="<?= $item->thumb_height ?>" width="<?= $item->thumb_width ?>" /></a><br />
          <? endif ?>
            <?= html::purify($item->description) ?>
          </p>
        ]]>
      </content:encoded>
      <media:thumbnail url="<?= $item->thumb_url(true) ?>"
                       height="<?= $item->thumb_height ?>"
                       width="<?= $item->thumb_width ?>"
                       />
    <? $view_full = access::can("view_full", $item); ?>
    <? if ($item->type == "photo" && $view_full): ?>
      <media:group>
    <? endif ?>
      <? if ($item->type == "photo"): ?>
        <media:content url="<?= $item->resize_url(true) ?>"
                       fileSize="<?= @filesize($item->resize_path()) ?>"
                       type="<?= $item->mime_type ?>"
                       height="<?= $item->resize_height ?>"
                       width="<?= $item->resize_width ?>"
                       />
      <? endif ?>
      <? if ($view_full): ?>
        <media:content url="<?= $item->file_url(true) ?>"
                       fileSize="<?= @filesize($item->file_path()) ?>"
                       type="<?= $item->mime_type ?>"
                       height="<?= $item->height ?>"
                       width="<?= $item->width ?>"
                       isDefault="true"
                       />
      <? endif ?>
    <? if ($item->type == "photo" && $view_full): ?>
      </media:group>
    <? endif ?>
    </item>
    <? endforeach ?>
  </channel>
</rss>
