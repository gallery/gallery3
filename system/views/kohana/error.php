<?php defined('SYSPATH') OR die('No direct access allowed.');
// Unique error identifier
$error_id = uniqid('error');
?>
<style type="text/css">
	
	#kohana_error {
		background: #CFF292;
		font-size: 1em;
		font-family: sans-serif;
		text-align: left;
		color: #111;
	}
	
	#kohana_error h1, #kohana_error h2 {
		margin: 0;
		padding: 1em;
		font-size: 1em;
		font-weight: normal;
		background: #CFF292;
		color: #000000;
	}
	
	#kohana_error h1 a, #kohana_error h2 a {
		color: #000;
	}
	
	#kohana_error h2 {
		background: #CFF292;
		border-top: 1px dotted;
	}
	
	#kohana_error h3 {
		margin: 0;
		padding: 0.4em 0 0;
		font-size: 1em;
		font-weight: normal;
	}
	
	#kohana_error p {
		margin: 0;
		padding: 0.2em 0;
	}
	
	#kohana_error a {
		color: #1b323b;
	}
	
	#kohana_error pre {
		overflow: auto;
		white-space: pre-wrap;
	}
	
	#kohana_error table {
		width: 100%;
		display: block;
		margin: 0 0 0.4em;
		padding: 0;
		border-collapse: collapse;
		background: #fff;
	}
	
	#kohana_error table td {
		border: solid 1px #ddd;
		text-align: left;
		vertical-align: top;
		padding: 0.4em;
	}
	
	#kohana_error div.content {
		padding: 0.4em 1em 1em;
		overflow: hidden;
		border-top: 1px dotted;
	}
	
	#kohana_error pre.source {
		margin: 0 0 1em;
		padding: 0.4em;
		background: #fff;
		border: dotted 1px #b7c680;
		line-height: 1.2em;
	}
	
	#kohana_error pre.source span.line {
		display: block;
	}
	
	#kohana_error pre.source span.highlight {
		background: #f0eb96;
	}
	
	#kohana_error pre.source span.line span.number {
		color: #666;
	}
	
	#kohana_error ol.trace {
		display: block;
		margin: 0 0 0 2em;
		padding: 0;
		list-style: decimal;
	}
	
	#kohana_error ol.trace li {
		margin: 0;
		padding: 0;
	}
</style>
<script type="text/javascript">
	document.write('<style type="text/css"> .collapsed { display: none; } </style>');
	function koggle(elem)
	{
		elem = document.getElementById(elem);
		
		if (elem.style && elem.style['display']) 
			// Only works with the "style" attr
			var disp = elem.style['display'];
		else 
			if (elem.currentStyle) 
				// For MSIE, naturally
				var disp = elem.currentStyle['display'];
			else 
				if (window.getComputedStyle) 
					// For most other browsers
					var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');
		
		// Toggle the state of the "display" style
		elem.style.display = disp == 'block' ? 'none' : 'block';
		return false;
	}
</script>
<div id="kohana_error">
	<h1>
		<span class="type">
<?php echo $type?> [ <?php echo $code?> ]:
		</span>
		<span class="message">
<?php echo $message?>
		</span>
	</h1>
	<div id="<?php echo $error_id ?>" class="content">
		<p>
			<span class="file">
<?php echo Kohana_Exception::debug_path($file)?>[ <?php echo $line?> ]
			</span>
		</p>

<?php if (Kohana_Exception::$source_output AND $source_code = Kohana_Exception::debug_source($file, $line)) : ?>
		<pre class="source"><code><?php foreach ($source_code as $num => $row) : ?><span class="line <?php if ($num == $line) echo 'highlight' ?>"><span class="number"><?php echo $num ?></span><?php echo htmlspecialchars($row, ENT_NOQUOTES, Kohana::CHARSET) ?></span><?php endforeach ?></code></pre>
<?php endif ?>

<?php if (Kohana_Exception::$trace_output) : ?>
		<ol class="trace">
			<?php foreach (Kohana_Exception::trace($trace) as $i=>$step): ?>
			<li>
				<p>
					<span class="file">
						<?php if ($step['file']): $source_id = $error_id.'source'.$i; ?>
						<?php if (Kohana_Exception::$source_output AND $step['source']) : ?>
						<a href="#<?php echo $source_id ?>" onclick="return koggle('<?php echo $source_id ?>')"><?php echo Kohana_Exception::debug_path($step['file'])?>[ <?php echo $step['line']?> ]</a>
						<?php else : ?>
						<span class="file"><?php echo Kohana_Exception::debug_path($step['file'])?>[ <?php echo $step['line']?> ]</span>
						<?php endif ?>
						<?php else : ?>
						{<?php echo __('PHP internal call')?>}
						<?php endif?>
					</span>
					&raquo;
					<?php echo $step['function']?>(<?php if ($step['args']): $args_id = $error_id.'args'.$i; ?><a href="#<?php echo $args_id ?>" onclick="return koggle('<?php echo $args_id ?>')"><?php echo __('arguments')?></a>
<?php endif?>)
				</p>
				<?php if (isset($args_id)): ?>
				<div id="<?php echo $args_id ?>" class="collapsed">
					<table cellspacing="0">
						<?php foreach ($step['args'] as $name=>$arg): ?>
						<tr>
							<td>
								<code>
<?php echo $name?>
								</code>
							</td>
							<td>
								<pre><?php echo Kohana_Exception::dump($arg) ?></pre>
							</td>
						</tr>
						<?php endforeach?>
					</table>
				</div>
				<?php endif?>
				<?php if (Kohana_Exception::$source_output AND $step['source'] AND isset($source_id)): ?>
				<pre id="<?php echo $source_id ?>" class="source collapsed"><code><?php foreach ($step['source'] as $num => $row) : ?><span class="line <?php if ($num == $step['line']) echo 'highlight' ?>"><span class="number"><?php echo $num ?></span><?php echo htmlspecialchars($row, ENT_NOQUOTES, Kohana::CHARSET) ?></span><?php endforeach ?></code></pre>
				<?php endif?>
			</li>
			<?php unset($args_id, $source_id); ?>
			<?php endforeach?>
		</ol>
<?php endif ?>

	</div>
	<h2><a href="#<?php echo $env_id = $error_id.'environment' ?>" onclick="return koggle('<?php echo $env_id ?>')"><?php echo __('Environment')?></a></h2>
	<div id="<?php echo $env_id ?>" class="content collapsed">
		<?php $included = get_included_files()?>
		<h3><a href="#<?php echo $env_id = $error_id.'environment_included' ?>" onclick="return koggle('<?php echo $env_id ?>')"><?php echo __('Included files')?></a>(<?php echo count($included)?>)</h3>
		<div id="<?php echo $env_id ?>" class="collapsed">
			<table cellspacing="0">
				<?php foreach ($included as $file): ?>
				<tr>
					<td>
						<code>
<?php echo Kohana_Exception::debug_path($file)?>
						</code>
					</td>
				</tr>
				<?php endforeach?>
			</table>
		</div>
		<?php $included = get_loaded_extensions()?>
		<h3><a href="#<?php echo $env_id = $error_id.'environment_loaded' ?>" onclick="return koggle('<?php echo $env_id ?>')"><?php echo __('Loaded extensions')?></a>(<?php echo count($included)?>)</h3>
		<div id="<?php echo $env_id ?>" class="collapsed">
			<table cellspacing="0">
				<?php foreach ($included as $file): ?>
				<tr>
					<td>
						<code>
<?php echo Kohana_Exception::debug_path($file)?>
						</code>
					</td>
				</tr>
				<?php endforeach?>
			</table>
		</div>
		<?php foreach (array('_SESSION', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER') as $var): ?>
		<?php if ( empty($GLOBALS[$var]) OR ! is_array($GLOBALS[$var])) continue ?>
		<h3><a href="#<?php echo $env_id = $error_id.'environment'.strtolower($var) ?>" onclick="return koggle('<?php echo $env_id ?>')">$<?php echo $var?></a></h3>
		<div id="<?php echo $env_id ?>" class="collapsed">
			<table cellspacing="0">
				<?php foreach ($GLOBALS[$var] as $key=>$value): ?>
				<tr>
					<td>
						<code>
<?php echo $key?>
						</code>
					</td>
					<td>
						<pre><?php echo Kohana_Exception::dump($value) ?></pre>
					</td>
				</tr>
				<?php endforeach?>
			</table>
		</div>
		<?php endforeach?>
	</div>
</div>
