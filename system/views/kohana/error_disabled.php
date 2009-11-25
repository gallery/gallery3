<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title><?php echo htmlspecialchars(__('Unable to Complete Request'), ENT_QUOTES, Kohana::CHARSET) ?></title>
	</head>
	<body>
		<div id="framework_error" style="width:24em;margin:50px auto;">
			<h3 style="text-align:center"><?php echo htmlspecialchars(__('Unable to Complete Request'), ENT_QUOTES, Kohana::CHARSET) ?></h3>
			<p style="text-align:center">
<?php
	echo __('You can go to the <a href="%site%">home page</a> or <a href="%uri%">try again</a>.',
		array('%site%' => htmlspecialchars(url::site(), ENT_QUOTES, Kohana::CHARSET), '%uri%' => htmlspecialchars(url::site(Router::$current_uri), ENT_QUOTES, Kohana::CHARSET)));
?>
			</p>
		</div>
	</body>
</html>
