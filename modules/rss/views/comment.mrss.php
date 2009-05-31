<?php defined("SYSPATH") or die("No direct script access.") ?>
<? echo "<?xml version=\"1.0\" ?>" ?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/"
   xmlns:atom="http://www.w3.org/2005/Atom"
   xmlns:content="http://purl.org/rss/1.0/modules/content/"
   xmlns:fh="http://purl.org/syndication/history/1.0">
  <channel>
    <generator>gallery3</generator>
    <title><?= p::clean($title) ?></title>
    <link><?= $link ?></link>
    <description><?= p::clean($description) ?></description>
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
      <title><?= p::clean($child["title"]) ?></title>
      <link><?= p::clean($child["item_link"]) ?></link>
      <author><?= p::clean($child["author"]) ?></author>
      <guid isPermaLink="true"><?= $child["item_link"] ?></guid>
      <pubDate><?= $child["pub_date"] ?></pubDate>
      <content:encoded>
        <![CDATA[
          <p><?= p::clean($child["text"]) ?></p>
          <p>
            <img alt="" src="<?= $child["thumb_url"] ?>"
                 height="<?= $child["thumb_height"] ?>" width="<?= $child["thumb_width"] ?>" />
            <br />
          </p>
        ]]>
      </content:encoded>
    </item>
    <? endforeach ?>
  </channel>
</rss>
