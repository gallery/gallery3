<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title>Gallery3 Installer</title>
    <style>
      body {
        background: #999;
        font-family: Trebuchet MS;
      }

      div#outer {
        width: 650px;
        background: white;
        border: 1px solid black;
        margin: 0 auto;
        padding: -10px;
        height: 600px;
      }

      div#inner {
        padding: 0 1em 0 1em;
        margin: 0px;
      }

      h1, h2, h3 {
        margin-bottom: .1em;
      }
    </style>
  </head>
  <body>
    <div id="outer">
      <center>
        <img src="http://www.gallery2.org/gallery2.png"/>
      </center>
      <div id="inner">
        <h1> Gallery 3 installer </h1>
        <p>
          <?= $content ?>
        </p>
      </div>
    </div>
  </body>
</html>
