DROP TABLE IF EXISTS {access_caches};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {access_caches} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `item_id` int(9) DEFAULT NULL,
  `view_full_1` binary(1) NOT NULL DEFAULT '0',
  `edit_1` binary(1) NOT NULL DEFAULT '0',
  `add_1` binary(1) NOT NULL DEFAULT '0',
  `view_full_2` binary(1) NOT NULL DEFAULT '0',
  `edit_2` binary(1) NOT NULL DEFAULT '0',
  `add_2` binary(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`)
) AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO {access_caches} VALUES (1,1,'1','0','0','1','0','0');
DROP TABLE IF EXISTS {access_intents};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {access_intents} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `item_id` int(9) DEFAULT NULL,
  `view_1` binary(1) DEFAULT NULL,
  `view_full_1` binary(1) DEFAULT NULL,
  `edit_1` binary(1) DEFAULT NULL,
  `add_1` binary(1) DEFAULT NULL,
  `view_2` binary(1) DEFAULT NULL,
  `view_full_2` binary(1) DEFAULT NULL,
  `edit_2` binary(1) DEFAULT NULL,
  `add_2` binary(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO {access_intents} VALUES (1,1,'1','1','0','0','1','1','0','0');
DROP TABLE IF EXISTS {caches};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {caches} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `expiration` int(9) NOT NULL,
  `cache` longblob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `tags` (`tags`)
) DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS {comments};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {comments} (
  `author_id` int(9) DEFAULT NULL,
  `created` int(9) NOT NULL,
  `guest_email` varchar(128) DEFAULT NULL,
  `guest_name` varchar(128) DEFAULT NULL,
  `guest_url` varchar(255) DEFAULT NULL,
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `item_id` int(9) NOT NULL,
  `server_http_accept_charset` varchar(64) DEFAULT NULL,
  `server_http_accept_encoding` varchar(64) DEFAULT NULL,
  `server_http_accept_language` varchar(64) DEFAULT NULL,
  `server_http_accept` varchar(128) DEFAULT NULL,
  `server_http_connection` varchar(64) DEFAULT NULL,
  `server_http_host` varchar(64) DEFAULT NULL,
  `server_http_referer` varchar(255) DEFAULT NULL,
  `server_http_user_agent` varchar(128) DEFAULT NULL,
  `server_query_string` varchar(64) DEFAULT NULL,
  `server_remote_addr` varchar(40) DEFAULT NULL,
  `server_remote_host` varchar(255) DEFAULT NULL,
  `server_remote_port` varchar(16) DEFAULT NULL,
  `state` varchar(15) DEFAULT 'unpublished',
  `text` text,
  `updated` int(9) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS {failed_auths};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {failed_auths} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `count` int(9) NOT NULL,
  `name` varchar(255) NOT NULL,
  `time` int(9) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS {graphics_rules};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {graphics_rules} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) DEFAULT '0',
  `args` varchar(255) DEFAULT NULL,
  `module_name` varchar(64) NOT NULL,
  `operation` varchar(64) NOT NULL,
  `priority` int(9) NOT NULL,
  `target` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO {graphics_rules} VALUES (1,1,'a:3:{s:5:\"width\";i:200;s:6:\"height\";i:200;s:6:\"master\";i:2;}','gallery','gallery_graphics::resize',100,'thumb');
INSERT INTO {graphics_rules} VALUES (2,1,'a:3:{s:5:\"width\";i:640;s:6:\"height\";i:640;s:6:\"master\";i:2;}','gallery','gallery_graphics::resize',100,'resize');
DROP TABLE IF EXISTS {groups};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {groups} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` char(64) DEFAULT NULL,
  `special` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO {groups} VALUES (1,'Everybody',1);
INSERT INTO {groups} VALUES (2,'Registered Users',1);
DROP TABLE IF EXISTS {groups_users};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {groups_users} (
  `group_id` int(9) NOT NULL,
  `user_id` int(9) NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`),
  UNIQUE KEY `user_id` (`user_id`,`group_id`)
) DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO {groups_users} VALUES (1,1);
INSERT INTO {groups_users} VALUES (1,2);
INSERT INTO {groups_users} VALUES (2,2);
DROP TABLE IF EXISTS {incoming_translations};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {incoming_translations} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `key` char(32) NOT NULL,
  `locale` char(10) NOT NULL,
  `message` text NOT NULL,
  `revision` int(9) DEFAULT NULL,
  `translation` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`,`locale`),
  KEY `locale_key` (`locale`,`key`)
) DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS {items};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {items} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `album_cover_item_id` int(9) DEFAULT NULL,
  `captured` int(9) DEFAULT NULL,
  `created` int(9) DEFAULT NULL,
  `description` text,
  `height` int(9) DEFAULT NULL,
  `left_ptr` int(9) NOT NULL,
  `level` int(9) NOT NULL,
  `mime_type` varchar(64) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `owner_id` int(9) DEFAULT NULL,
  `parent_id` int(9) NOT NULL,
  `rand_key` decimal(11,10) DEFAULT NULL,
  `relative_path_cache` varchar(255) DEFAULT NULL,
  `relative_url_cache` varchar(255) DEFAULT NULL,
  `resize_dirty` tinyint(1) DEFAULT '1',
  `resize_height` int(9) DEFAULT NULL,
  `resize_width` int(9) DEFAULT NULL,
  `right_ptr` int(9) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `sort_column` varchar(64) DEFAULT NULL,
  `sort_order` char(4) DEFAULT 'ASC',
  `thumb_dirty` tinyint(1) DEFAULT '1',
  `thumb_height` int(9) DEFAULT NULL,
  `thumb_width` int(9) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` varchar(32) NOT NULL,
  `updated` int(9) DEFAULT NULL,
  `view_count` int(9) DEFAULT '0',
  `weight` int(9) NOT NULL DEFAULT '0',
  `width` int(9) DEFAULT NULL,
  `view_1` binary(1) DEFAULT '0',
  `view_2` binary(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `type` (`type`),
  KEY `random` (`rand_key`),
  KEY `weight` (`weight`),
  KEY `left_ptr` (`left_ptr`),
  KEY `relative_path_cache` (`relative_path_cache`)
) AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO {items} VALUES (1,NULL,NULL,UNIX_TIMESTAMP(),'',NULL,1,1,NULL,NULL,2,0,NULL,'','',1,NULL,NULL,2,NULL,'weight','ASC',1,NULL,NULL,'Gallery','album',UNIX_TIMESTAMP(),0,1,NULL,'1','1');
DROP TABLE IF EXISTS {items_tags};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {items_tags} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `item_id` int(9) NOT NULL,
  `tag_id` int(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tag_id` (`tag_id`,`id`),
  KEY `item_id` (`item_id`,`id`)
) DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS {logs};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {logs} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `category` varchar(64) DEFAULT NULL,
  `html` varchar(255) DEFAULT NULL,
  `message` text,
  `referer` varchar(255) DEFAULT NULL,
  `severity` int(9) DEFAULT '0',
  `timestamp` int(9) DEFAULT '0',
  `url` varchar(255) DEFAULT NULL,
  `user_id` int(9) DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS {messages};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {messages} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) DEFAULT NULL,
  `severity` varchar(32) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS {modules};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {modules} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) DEFAULT '0',
  `name` varchar(64) DEFAULT NULL,
  `version` int(9) DEFAULT NULL,
  `weight` int(9) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `weight` (`weight`)
) AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO {modules} VALUES (1,1,'gallery',57,1);
INSERT INTO {modules} VALUES (2,1,'user',4,2);
INSERT INTO {modules} VALUES (3,1,'comment',7,3);
INSERT INTO {modules} VALUES (4,1,'organize',4,4);
INSERT INTO {modules} VALUES (5,1,'info',2,5);
INSERT INTO {modules} VALUES (6,1,'rss',1,6);
INSERT INTO {modules} VALUES (7,1,'search',1,7);
INSERT INTO {modules} VALUES (8,1,'slideshow',2,8);
INSERT INTO {modules} VALUES (9,1,'tag',3,9);
DROP TABLE IF EXISTS {outgoing_translations};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {outgoing_translations} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `base_revision` int(9) DEFAULT NULL,
  `key` char(32) NOT NULL,
  `locale` char(10) NOT NULL,
  `message` text NOT NULL,
  `translation` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`,`locale`),
  KEY `locale_key` (`locale`,`key`)
) DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS {permissions};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {permissions} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `display_name` varchar(64) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO {permissions} VALUES (1,'View','view');
INSERT INTO {permissions} VALUES (2,'View full size','view_full');
INSERT INTO {permissions} VALUES (3,'Edit','edit');
INSERT INTO {permissions} VALUES (4,'Add','add');
DROP TABLE IF EXISTS {search_records};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {search_records} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `item_id` int(9) DEFAULT NULL,
  `dirty` tinyint(1) DEFAULT '1',
  `data` longtext,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  FULLTEXT KEY `data` (`data`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO {search_records} VALUES (1,1,0,'  Gallery');
DROP TABLE IF EXISTS {sessions};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {sessions} (
  `session_id` varchar(127) NOT NULL,
  `data` text NOT NULL,
  `last_activity` int(10) unsigned NOT NULL,
  PRIMARY KEY (`session_id`)
) DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS {tags};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {tags} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS {tasks};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {tasks} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `callback` varchar(128) DEFAULT NULL,
  `context` text NOT NULL,
  `done` tinyint(1) DEFAULT '0',
  `name` varchar(128) DEFAULT NULL,
  `owner_id` int(9) DEFAULT NULL,
  `percent_complete` int(9) DEFAULT '0',
  `state` varchar(32) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `updated` int(9) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`)
) DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS {themes};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {themes} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `version` int(9) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO {themes} VALUES (1,'wind',1);
INSERT INTO {themes} VALUES (2,'admin_wind',1);
DROP TABLE IF EXISTS {users};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {users} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `password` varchar(64) NOT NULL,
  `login_count` int(10) unsigned NOT NULL DEFAULT '0',
  `last_login` int(10) unsigned NOT NULL DEFAULT '0',
  `email` varchar(64) DEFAULT NULL,
  `admin` tinyint(1) DEFAULT '0',
  `guest` tinyint(1) DEFAULT '0',
  `hash` char(32) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `locale` char(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `hash` (`hash`)
) AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO {users} VALUES (1,'guest','Guest User','',0,0,NULL,0,1,NULL,NULL,NULL);
INSERT INTO {users} VALUES (2,'admin','Gallery Administrator','',0,0,'unknown@unknown.com',1,0,NULL,NULL,NULL);
DROP TABLE IF EXISTS {vars};
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE {vars} (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_name` (`module_name`,`name`)
) AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO {vars} VALUES (NULL,'gallery','active_site_theme','wind');
INSERT INTO {vars} VALUES (NULL,'gallery','active_admin_theme','admin_wind');
INSERT INTO {vars} VALUES (NULL,'gallery','page_size','9');
INSERT INTO {vars} VALUES (NULL,'gallery','thumb_size','200');
INSERT INTO {vars} VALUES (NULL,'gallery','resize_size','640');
INSERT INTO {vars} VALUES (NULL,'gallery','default_locale','en_US');
INSERT INTO {vars} VALUES (NULL,'gallery','image_quality','75');
INSERT INTO {vars} VALUES (NULL,'gallery','image_sharpen','15');
INSERT INTO {vars} VALUES (NULL,'gallery','upgrade_checker_auto_enabled','1');
INSERT INTO {vars} VALUES (NULL,'gallery','blocks_dashboard_sidebar','a:4:{i:2;a:2:{i:0;s:7:\"gallery\";i:1;s:11:\"block_adder\";}i:3;a:2:{i:0;s:7:\"gallery\";i:1;s:5:\"stats\";}i:4;a:2:{i:0;s:7:\"gallery\";i:1;s:13:\"platform_info\";}i:5;a:2:{i:0;s:7:\"gallery\";i:1;s:12:\"project_news\";}}');
INSERT INTO {vars} VALUES (NULL,'gallery','blocks_dashboard_center','a:4:{i:6;a:2:{i:0;s:7:\"gallery\";i:1;s:7:\"welcome\";}i:7;a:2:{i:0;s:7:\"gallery\";i:1;s:15:\"upgrade_checker\";}i:8;a:2:{i:0;s:7:\"gallery\";i:1;s:12:\"photo_stream\";}i:9;a:2:{i:0;s:7:\"gallery\";i:1;s:11:\"log_entries\";}}');
INSERT INTO {vars} VALUES (NULL,'gallery','choose_default_tookit','1');
INSERT INTO {vars} VALUES (NULL,'gallery','date_format','Y-M-d');
INSERT INTO {vars} VALUES (NULL,'gallery','date_time_format','Y-M-d H:i:s');
INSERT INTO {vars} VALUES (NULL,'gallery','time_format','H:i:s');
INSERT INTO {vars} VALUES (NULL,'gallery','show_credits','1');
INSERT INTO {vars} VALUES (NULL,'gallery','credits','Powered by <a href=\"%url\">%gallery_version</a>');
INSERT INTO {vars} VALUES (NULL,'gallery','simultaneous_upload_limit','5');
INSERT INTO {vars} VALUES (NULL,'gallery','admin_area_timeout','5400');
INSERT INTO {vars} VALUES (NULL,'gallery','maintenance_mode','0');
INSERT INTO {vars} VALUES (NULL,'gallery','visible_title_length','15');
INSERT INTO {vars} VALUES (NULL,'gallery','favicon_url','lib/images/favicon.ico');
INSERT INTO {vars} VALUES (NULL,'gallery','apple_touch_icon_url','lib/images/apple-touch-icon.png');
INSERT INTO {vars} VALUES (NULL,'gallery','email_from','unknown@unknown.com');
INSERT INTO {vars} VALUES (NULL,'gallery','email_reply_to','unknown@unknown.com');
INSERT INTO {vars} VALUES (NULL,'gallery','email_line_length','70');
INSERT INTO {vars} VALUES (NULL,'gallery','email_header_separator','s:1:\"\n\";');
INSERT INTO {vars} VALUES (NULL,'gallery','show_user_profiles_to','registered_users');
INSERT INTO {vars} VALUES (NULL,'gallery','extra_binary_paths','/usr/local/bin:/opt/local/bin:/opt/bin');
INSERT INTO {vars} VALUES (NULL,'gallery','timezone',NULL);
INSERT INTO {vars} VALUES (NULL,'gallery','lock_timeout','1');
INSERT INTO {vars} VALUES (NULL,'gallery','movie_extract_frame_time','3');
INSERT INTO {vars} VALUES (NULL,'gallery','movie_allow_uploads','autodetect');
INSERT INTO {vars} VALUES (NULL,'gallery','blocks_site_sidebar','a:4:{i:10;a:2:{i:0;s:7:\"gallery\";i:1;s:8:\"language\";}i:11;a:2:{i:0;s:4:\"info\";i:1;s:8:\"metadata\";}i:12;a:2:{i:0;s:3:\"rss\";i:1;s:9:\"rss_feeds\";}i:13;a:2:{i:0;s:3:\"tag\";i:1;s:3:\"tag\";}}');
INSERT INTO {vars} VALUES (NULL,'gallery','identity_provider','user');
INSERT INTO {vars} VALUES (NULL,'user','minimum_password_length','5');
INSERT INTO {vars} VALUES (NULL,'comment','spam_caught','0');
INSERT INTO {vars} VALUES (NULL,'comment','access_permissions','everybody');
INSERT INTO {vars} VALUES (NULL,'comment','rss_visible','all');
INSERT INTO {vars} VALUES (NULL,'info','show_title','1');
INSERT INTO {vars} VALUES (NULL,'info','show_description','1');
INSERT INTO {vars} VALUES (NULL,'info','show_owner','1');
INSERT INTO {vars} VALUES (NULL,'info','show_name','1');
INSERT INTO {vars} VALUES (NULL,'info','show_captured','1');
INSERT INTO {vars} VALUES (NULL,'slideshow','max_scale','0');
INSERT INTO {vars} VALUES (NULL,'tag','tag_cloud_size','30');
