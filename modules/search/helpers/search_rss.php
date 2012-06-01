<?php defined("SYSPATH") or die("No direct script access.");
class search_rss_Core {
  static function available_feeds($item, $tag) {
  	$feeds = array();

    return $feeds;
  }
  static function feed($feed_id, $offset, $limit, $id) {
  	$feed = new stdClass();
  	if ($feed_id == "search") 
  	{
  		$q = $id;
  		$page_size = $limit;
    	$q_with_more_terms = search::add_query_terms($q);
    	list ($count, $result) = search::search($q_with_more_terms, $page_size, $offset);
    	$feed->items = $result;
    	$feed->max_pages = max(ceil($count / $page_size), 1);
    	$feed->title = t("Search: %q", array("q" => $q_with_more_terms));
    	return $feed;
    }
  }
}