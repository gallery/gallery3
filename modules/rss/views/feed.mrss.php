<?php defined("SYSPATH") or die("No direct script access.") ?>
<? echo "<?xml version=\"1.0\" ?>" ?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/"
   xmlns:atom="http://www.w3.org/2005/Atom"
   xmlns:content="http://purl.org/rss/1.0/modules/content"
   xmlns:fh="http://purl.org/syndication/history/1.0">
  <channel>
    <generator>gallery3</generator>
    <title><?= htmlspecialchars($title) ?></title>
    <link><?= $link ?></link>
    <description><?= htmlspecialchars($description) ?></description>
    <language>en-us</language>
    <atom:link rel="self" href="<?= $feed_link ?>" type="application/rss+xml" />
    <fh:complete/>
    <? if (!empty($previous_page_link)): ?>
    <atom:link rel="previous" href="<?= $previous_page_link ?>" type="application/rss+xml" />
    <? endif ?>
    <? if (!empty($next_page_link)): ?>
    <atom:link rel="next" href="<?= $next_page_link ?>" type="application/rss+xml" />
    <? endif ?>
    <pubDate><?= $pub_date ?></pubDate>
    <lastBuildDate><?= $pub_date ?></lastBuildDate>
    <? foreach ($children as $child): ?>
    <item>
      <title><?= htmlspecialchars($child->title) ?></title>
      <link><?= url::abs_site("photos/$child->id") ?></link>
      <guid isPermaLink="true"><?= url::abs_site("photos/$child->id") ?></guid>
      <description><?= htmlspecialchars($child->description) ?></description>
      <enclosure url="<?= $child->file_url(true) ?>"
                 type="<?= $child->mime_type ?>"
                 height="<?= $child->height ?>"
                 width="<?= $child->width ?>"/>
      <media:thumbnail url="<?= $child->thumb_url(true) ?>"
                       fileSize="<?= filesize($child->thumb_path()) ?>"
                       height="<?= $child->thumb_height ?>"
                       width="<?= $child->thumb_width ?>"
                       />
       <content:encoded>
         <![CDATA[
           <p>
              <img alt="" src="<?= $child->resize_url(true) ?>"
                   title="<?= htmlspecialchars($child->title) ?>"
                   height="<?= $child->resize_height ?>" width="<?= $child->resize_width ?>" /><br />
              <?= $child->description ?>
            </p>
         ]]>
       </content:encoded>
      <media:group>
        <media:content url="<?= $child->resize_url(true) ?>"
                       fileSize="<?= filesize($child->resize_path()) ?>"
                       type="<?= $child->mime_type ?>"
                       height="<?= $child->resize_height ?>"
                       width="<?= $child->resize_width ?>"
                       isDefault="true"
                       />
       <? if (access::can("view_full", $child)): ?>
       <media:content url="<?= $child->file_url(true) ?>"
                       fileSize="<?= filesize($child->file_path()) ?>"
                       type="<?= $child->mime_type ?>"
                       height="<?= $child->height ?>"
                       width="<?= $child->width ?>"
                       />
       <? endif ?>
      </media:group>
    </item>
    <? endforeach ?>
  </channel>
</rss>
