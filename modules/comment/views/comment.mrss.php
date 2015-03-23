<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php echo "<?xml version=\"1.0\" ?>" ?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/"
   xmlns:atom="http://www.w3.org/2005/Atom"
   xmlns:content="http://purl.org/rss/1.0/modules/content/"
   xmlns:fh="http://purl.org/syndication/history/1.0">
  <channel>
    <generator>Gallery 3</generator>
    <title><?php echo html::clean($feed->title) ?></title>
    <link><?php echo $feed->uri ?></link>
    <description><?php echo html::clean($feed->description) ?></description>
    <language>en-us</language>
    <atom:link rel="self" href="<?php echo $feed->uri ?>" type="application/rss+xml" />
    <fh:complete/>
    <?php if (!empty($feed->previous_page_uri)): ?>
    <atom:link rel="previous" href="<?php echo $feed->previous_page_uri ?>" type="application/rss+xml" />
    <?php endif ?>
    <?php if (!empty($feed->next_page_uri)): ?>
    <atom:link rel="next" href="<?php echo $feed->next_page_uri ?>" type="application/rss+xml" />
    <?php endif ?>
    <pubDate><?php echo $pub_date ?></pubDate>
    <lastBuildDate><?php echo $pub_date ?></lastBuildDate>
    <?php foreach ($feed->comments as $comment): ?>
    <item>
      <title><?php echo html::purify($comment->title) ?></title>
      <link><?php echo html::clean($comment->item_uri) ?></link>
      <author><?php echo html::clean($comment->author) ?></author>
      <guid isPermaLink="true"><?php echo $comment->item_uri ?></guid>
      <pubDate><?php echo $comment->pub_date ?></pubDate>
      <content:encoded>
        <![CDATA[
          <p><?php echo nl2br(html::purify($comment->text)) ?></p>
          <p>
            <img alt="" src="<?php echo $comment->thumb_url ?>"
                 height="<?php echo $comment->thumb_height ?>" width="<?php echo $comment->thumb_width ?>" />
            <br />
          </p>
        ]]>
      </content:encoded>
    </item>
    <?php endforeach ?>
  </channel>
</rss>
