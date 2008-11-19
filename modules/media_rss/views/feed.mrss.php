<? defined("SYSPATH") or die("No direct script access."); ?>
<? echo "<?xml version=\"1.0\" ?>"; ?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/"
                   xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title><?= $item->title ?></title>
    <link><?= url::abs_site("albums/{$item->id}") ?></link>
    <description><?= $item->description ?></description>
    <language>en-us</language>
    <atom:link rel="self" href="<?= url::abs_site("media_rss/feed/{$item->id}") ?>" type="application/rss+xml" />
    <? if (isset($prevOffset)): ?>
      <atom:link rel="previous" href="<?= url::abs_site("media_rss/feed/{$item->id}?offset={$prevOffset}") ?>"
        type="application/rss+xml" />
    <? endif; ?>
    <? if (isset($nextOffset)): ?>
      <atom:link rel="next" href="<?= url::abs_site("media_rss/feed/{$item->id}?offset={$nextOffset}") ?>"
        type="application/rss+xml" />
    <? endif; ?>
    <?
      // @todo do we want to add an upload date to the items table?
      $date = date("D, d M Y H:i:s T");
    ?>
    <pubDate><?= $date ?></pubDate>
    <lastBuildDate><?= $date ?></lastBuildDate>
    <? foreach ($children as $child): ?>
      <item>
        <title><?= $child->title ?></title>
        <link><?= url::abs_site("photos/$child->id") ?></link>
        <guid isPermaLink="false"><?= $child->id ?></guid>
        <description><?= $child->description ?></description>
        <media:group>
          <media:thumbnail  url="<?= $child->thumbnail_url(true) ?>"
            height="<?= $child->thumbnail_height ?>"
            width="<?= $child->thumbnail_width ?>"
           />
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
    <? endforeach; ?>
  </channel>
</rss>
