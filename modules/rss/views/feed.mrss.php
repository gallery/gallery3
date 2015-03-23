<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php echo '<?xml version="1.0" ?>' ?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/"
   xmlns:atom="http://www.w3.org/2005/Atom"
   xmlns:content="http://purl.org/rss/1.0/modules/content/"
   xmlns:fh="http://purl.org/syndication/history/1.0">
  <channel>
    <generator>gallery3</generator>
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
    <?php foreach ($feed->items as $item): ?>
    <item>
      <title><?php echo html::purify($item->title) ?></title>
      <link><?php echo url::abs_site("{$item->type}s/{$item->id}") ?></link>
      <guid isPermaLink="true"><?php echo url::abs_site("{$item->type}s/{$item->id}") ?></guid>
      <pubDate><?php echo date("D, d M Y H:i:s O", $item->created); ?></pubDate>
      <description><?php echo html::purify($item->description) ?></description>
      <content:encoded>
        <![CDATA[
          <span><?php echo html::purify($item->description) ?></span>
          <p>
          <?php if ($item->type == "photo"): ?>
            <img alt="" src="<?php echo $item->resize_url(true) ?>"
                 title="<?php echo html::purify($item->title)->for_html_attr() ?>"
                 height="<?php echo $item->resize_height ?>" width="<?php echo $item->resize_width ?>" /><br />
          <?php else: ?>
            <a href="<?php echo url::abs_site("{$item->type}s/{$item->id}") ?>">
            <img alt="" src="<?php echo $item->thumb_url(true) ?>"
                 title="<?php echo html::purify($item->title)->for_html_attr() ?>"
                 height="<?php echo $item->thumb_height ?>" width="<?php echo $item->thumb_width ?>" /></a><br />
          <?php endif ?>
            <?php echo html::purify($item->description) ?>
          </p>
        ]]>
      </content:encoded>
      <media:thumbnail url="<?php echo $item->thumb_url(true) ?>"
                       height="<?php echo $item->thumb_height ?>"
                       width="<?php echo $item->thumb_width ?>"
                       />
    <?php $view_full = access::can("view_full", $item); ?>
    <?php if ($item->type == "photo" && $view_full): ?>
      <media:group>
    <?php endif ?>
      <?php if ($item->type == "photo"): ?>
        <media:content url="<?php echo $item->resize_url(true) ?>"
                       fileSize="<?php echo @filesize($item->resize_path()) ?>"
                       type="<?php echo $item->mime_type ?>"
                       height="<?php echo $item->resize_height ?>"
                       width="<?php echo $item->resize_width ?>"
                       />
      <?php endif ?>
      <?php if ($view_full): ?>
        <media:content url="<?php echo $item->file_url(true) ?>"
                       fileSize="<?php echo @filesize($item->file_path()) ?>"
                       type="<?php echo $item->mime_type ?>"
                       height="<?php echo $item->height ?>"
                       width="<?php echo $item->width ?>"
                       isDefault="true"
                       />
      <?php endif ?>
    <?php if ($item->type == "photo" && $view_full): ?>
      </media:group>
    <?php endif ?>
    </item>
    <?php endforeach ?>
  </channel>
</rss>
