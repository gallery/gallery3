<?php defined("SYSPATH") or die("No direct script access.") ?>
<? echo '<?xml version="1.0" encoding="UTF-8" ?>' ?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/"
   xmlns:atom="http://www.w3.org/2005/Atom"
   xmlns:content="http://purl.org/rss/1.0/modules/content/"
   xmlns:fh="http://purl.org/syndication/history/1.0">
  <channel>
    <generator>Gallery 3</generator>
    <title><?= HTML::clean($feed->title) ?></title>
    <link><?= $feed->uri ?></link>
    <description><?= HTML::clean($feed->description) ?></description>
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
    <? foreach ($feed->comments as $comment): ?>
    <item>
      <title><?= HTML::purify($comment->title) ?></title>
      <link><?= HTML::clean($comment->item_uri) ?></link>
      <author><?= HTML::clean($comment->author) ?></author>
      <guid isPermaLink="true"><?= $comment->item_uri ?></guid>
      <pubDate><?= $comment->pub_date ?></pubDate>
      <content:encoded>
        <![CDATA[
          <p><?= nl2br(HTML::purify($comment->text)) ?></p>
          <p>
            <img alt="" src="<?= $comment->thumb_url ?>"
                 height="<?= $comment->thumb_height ?>" width="<?= $comment->thumb_width ?>" />
            <br />
          </p>
        ]]>
      </content:encoded>
    </item>
    <? endforeach ?>
  </channel>
</rss>
