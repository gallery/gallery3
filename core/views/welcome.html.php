<? defined("SYSPATH") or die("No direct script access."); ?>
<html>
  <head>
    <title>Gallery3 Scaffold</title>
    <style>
      body {
        background: #999;
        font-family: Trebuchet MS;
      }

      div.outer {
        width: 600px;
        background: white;
        border: 1px solid black;
        margin: 0 auto;
        padding: -10px;
      }

      div.inner {
        padding: 0 1em 0 1em;
        margin: 0px;
      }

      h1, h2, h3 {
        margin-bottom: .1em;
      }

      p {
        margin: 0 0 0 0;
      }

      pre {
        margin: 0;
        padding-left: 2em;
      }

      .error {
        color: red;
      }

      .success {
        color: green;
      }

      p.success {
        font-weight: bold;
      }

      div.block {
        padding: 0px;
        padding-left: 1em;
        margin: 0px;
        padding-bottom: 1em;
      }

      ul {
        margin-top: -.25em;
      }
    </style>
  </head>
  <body>
    <div class="outer">
      <center>
	<img src="http://www.gallery2.org/gallery2.png"/>
      </center>
      <div class="inner">
	<h1>Gallery3 Scaffold</h1>

	<p>
	  This is
	  a <b><a href="http://www.google.com/images?q=scaffold">scaffold</a></b>:
	  a <i>temporary structure built to support the developers as
	  they create the real product</i>.
	</p>

	<p>
	  As we flesh out Gallery 3, we'll make it possible for you to
	  peer inside and see the application taking shape.
	  Eventually, this page will go away and you'll start in the
	  application itself.  In the meantime, here are some useful
	  links to get you started.
	</p>

	<h2>System Configuration</h2>
	<?= $syscheck ?>

	<h2>Activities</h2>
	<p>
	</p>

	<h2>Documentation</h2>
	<ul>
	  <li>
	    <a href="http://docs.google.com/Doc?id=dfjxt593_184ff9jhmd8&hl=en">Gallery3: Prioritized Feature List</a>
	  </li>
	  <li>
	    <a href="http://docs.google.com/Doc?id=dfjxt593_185czczpm4f&hl=en">Gallery3: Secondary Features</a>
	  </li>
	  <li>
	    <a href="http://gallery.svn.sourceforge.net/viewvc/gallery/trunk/eval/gx/ui/HTML/index.html">Mockups</a>
	  </li>
	  <li>
	    <a href="http://www.nabble.com/Rough-Gallery-3-time-line-td20240153.html">Rough Timeline</a> (as of Oct 29, 2008)
	  </li>
	  <li>
	    <a href="http://codex.gallery2.org/Gallery3:About">Gallery3: About Page</a>
	  </li>
	  <li>
	    <a href="http://codex.gallery2.org/Gallery3:Coding_Standards">Gallery3: Coding Standards</a>
	  </li>
	</ul>
      </div>
    </div>
  </body>
</html>
