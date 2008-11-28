<?php defined('SYSPATH') or die('No direct script access.');
Event::add("gallery.photo.created", array('tag', 'on_photo_create'));
