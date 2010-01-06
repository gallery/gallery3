<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Inflector Config. Lists of words that are uncountable or irregular.
 * If you would like to add a word to these lists please open a new issue on the
 * [issue tracker](http://dev.kohanaphp.com/projects/kohana2/issues)
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

$config['uncountable'] = array
(
	'access',
	'advice',
	'art',
	'baggage',
	'dances',
	'data',
	'equipment',
	'fish',
	'fuel',
	'furniture',
	'food',
	'heat',
	'honey',
	'homework',
	'impatience',
	'information',
	'knowledge',
	'luggage',
	'metadata',
	'money',
	'music',
	'news',
	'patience',
	'progress',
	'pollution',
	'research',
	'rice',
	'sand',
	'series',
	'sheep',
	'sms',
	'species',
	'staff',
	'toothpaste',
	'traffic',
	'understanding',
	'water',
	'weather',
	'work',
);

$config['irregular'] = array
(
	'child' => 'children',
	'clothes' => 'clothing',
	'man' => 'men',
	'movie' => 'movies',
	'person' => 'people',
	'woman' => 'women',
	'mouse' => 'mice',
	'goose' => 'geese',
	'ox' => 'oxen',
	'leaf' => 'leaves',
	'course' => 'courses',
	'size' => 'sizes',
);
