<?php defined("SYSPATH") or die("No direct script access."); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
   <head>
	
     <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	
     <title>Gallery3 Requirements Verification</title>
	
     <style type="text/css">
       body { width: 42em; margin: 0 auto; font-family: sans-serif; font-size: 90%; }
	
       #tests table { border-collapse: collapse; width: 100%; }
       #tests table th,
       #tests table td { padding: 0.2em 0.4em; text-align: left; vertical-align: top; }
       #tests table th { width: 12em; font-weight: normal; font-size: 1.2em; }
       #tests table tr:nth-child(odd) { background: #eee; }
       #tests table td.pass { color: #191; }
       #tests table td.fail { color: #911; }
       #tests #results { color: #fff; }
       #tests #results p { padding: 0.8em 0.4em; }
       #tests #results p.pass { background: #191; }
       #tests #results p.fail { background: #911; }
     </style>

  </head>
  <body>
  <? foreach (self::$messages as $section) : ?>
    <h1><?php print $section["header"] ?></h1>
	
    <p><?php print $section["description"] ?></p>

   <div id="tests">

   <table cellspacing="0">
     <?php foreach ($section["msgs"] as $header => $msg): ?>
      
     <tr>
       <th><?php echo $header ?></th>
       <td class="<?php echo empty($msg["error"]) ? "pass" : "fail" ?>">
       <?php echo empty($msg["html"]) ? $msg["text"] : $msg["html"] ?>
       </td>
     </tr>
     <?php endforeach ?>
   </table>
 </div>
 <? endforeach ?>
 </body>
 </html>