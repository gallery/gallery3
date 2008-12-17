<? defined("SYSPATH") or die("No direct script access."); ?>
<? echo "<?xml version=\"1.0\" ?>" ?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/"
     xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title><? $title ?></title>
    <link><?= $link ?></link>
    <description><?= $description ?></description>
    <language>en-us</language>
    <atom:link rel="self" href="<?= $feed_link ?>" type="application/rss+xml" />
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
      <title><?= $child->title ?></title>
      <link><?= url::abs_site("photos/$child->id") ?></link>
      <guid isPermaLink="true"><?= url::abs_site("photos/$child->id") ?></guid>
      <description><?= $child->description ?></description>
      <media:thumbnail url="<?= $child->thumb_url(true) ?>"
                       height="<?= $child->thumb_height ?>"
                       width="<?= $child->thumb_width ?>"
                       />
      <media:group>
        <media:content url="<?= $child->resize_url(true) ?>"
                       type="<?= $child->mime_type ?>"
                       height="<?= $child->resize_height ?>"
                       width="<?= $child->resize_width ?>"
                       isDefault="true"
                       />
        <media:content url="<?= $child->file_url(true) ?>"
                       type="<?= $child->mime_type ?>"
                       height="<?= $child->height ?>"
                       width="<?= $child->width ?>"
                       />
      </media:group>
    </item>
    <? endforeach ?>
  </channel>
</rss>
