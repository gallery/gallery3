<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-rest-admin" class="g-block ui-helper-clearfix">
  <h1> <?= t("REST API settings") ?> </h1>
  <p>
    <?= t("Gallery's REST API allows it to interface with desktop clients, mobile apps, photo software plug-ins, CMS embedding, and more.") ?>
  </p>
  <p>
    <?= t("The REST API adheres to the same access restrictions as the standard UI. In addition, you can choose if you want the REST API to:") ?><br/>
    <b><?= t("Allow guest access:") ?></b>
      <?= t("If disabled, guests will be blocked even if normally allowed.") ?><br/>
    <b><?= t("Allow write access:") ?></b>
      <?= t("If disabled, add/edit features will be blocked (read-only access).") ?><br/>
  </p>
  <p>
    <?= t("Gallery supports two ways of embedding into websites <i>on other domains</i> using REST:") ?><br/>
    <b><?= t("JSONP embedding:") ?></b>
      <?= t("This is widely supported, but restricted to read-only, guest-level access and offers no control over which domains can use it.") ?><br/>
    <b><?= t("CORS embedding:") ?></b>
      <?= t("This offers control over which domains can use it and permits both private and write access.") ?>
      <?= t("However, it isn't supported by older browsers (specifically IE7 and earlier).") ?>
  </p>
  <p>
    <?= t("Want to see what it looks like? Click <a href=\"%url\">here</a> to navigate your Gallery using your admin login in (read-only) HTML mode. The link includes your access key, so keep it private!",
      array("url" => $rest_url)) ?>
  </p>
  <p>
    <?= t("Confused or curious and want additional info? <a href=\"%url\">We have docs!</a>",
      array("url" => "http://codex.galleryproject.org/Gallery3:API:REST")) ?>
  </p>

  <?= $form ?>
</div>
