<? defined("SYSPATH") or die("No direct script access."); ?>
<? echo "<?xml version=\"1.0\" ?>"; ?>
<rss version="2.0">
  <channel>
    <title><?= $item->title ?></title>
    <link><?= url::site("slideshow/$item->id") ?></link>
    <description><?= $item->description ?></description>
    <language>en-us</language>
    <?
      // @todo do we want to add an upload date to the items table?
      $date = date("D, dd M Y H:i:s e");
    ?>
    <pubDate><?= $date ?></pubDate>
    <lastBuildDate><?= $date ?></lastBuildDate>
    <? foreach ($children as $child): ?>
      <image>
      </image>
    <? endforeach; ?>
  </channel>
</rss>