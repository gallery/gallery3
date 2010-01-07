<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * ##### Custom Routes
 * Before changing this file you should copy it to your application/config directory.
 *
 * [!!] Routes will run in the order they are defined. Higher routes will always take precedence over lower ones.
 *
 * __Default Route__
 *
 *     $config['_default'] = 'welcome';
 *
 * $config['_default'] specifies the default route. It is used to indicate which controller
 * should be used when a URI contains no segments. For example, if your web application is at
 * www.example.com and you visit this address with a web browser, the welcome controller would
 * be used even though it wasn't specified in the URI. The result would be the same as if the
 * browser had gone to www.example.com/welcome.
 *
 * __Custom Routes__
 *
 * In addition to the default route above, you can also specify your own routes. The basic
 * format for a routing rule is:
 *
 *     $config['route'] = 'class/method';
 *
 * Where *route* is the URI you want to route, and *class/method* would replace it.
 *
 * For example, if your Kohana web application was installed at www.example.com and
 * you had the following routing rule: `$config['test'] = 'foo/bar';`
 * Browsing to www.example.com/test would be *internally* redirected to www.example.com/foo/bar.
 *
 * __Advanced Routes with Regex__
 *
 * The route part of a routing rule is actually a regular expression. If you are unfamiliar
 * with regular expressions you can read more about them at the PHP website. Using regular expressions,
 * you can be more selective about which URIs will match your routing rules, and you can make use of the
 * sub-pattern back referencing technique to re-use parts of the URI in it's replacement.
 *
 * This is best described with an example. Suppose we wanted to make the URL www.example.com/article/22
 * work, we might use a routing rule like this:
 *
 *     $config['article/([0-9]+)'] = 'news/show/$1';
 *
 * which would match URIs starting with “article/” followed by some numeric digits. If the URI takes this
 * form, we will use the news controller and call it's show() method passing in the article number as the
 * first argument. In the www.example.com/article/22 example, it is as if the URL www.example.com/news/show/22
 * had been visited.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */


/**
 * Sets the default route to "welcome"
 */
$config['_default'] = 'welcome';
