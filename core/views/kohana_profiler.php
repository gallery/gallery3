<?php defined("SYSPATH") or die("No direct script access.") ?>
<style type="text/css">
  #kohana-profiler {
    background-color: #F8FFF8;
    border: 1px solid #E5EFF8;
    clear: both;
    font-family: Monaco, 'Courier New';
    margin-top: 20px;
    padding: 10px 10px 0;
    text-align: left;
  }
  #kohana-profiler pre {
    font: inherit;
    margin: 0;
  }
  #kohana-profiler .kp-meta {
    background: #fff;
    border: 1px solid #E5EFF8;
    color: #A6B0B8;
    margin: 0 0 10px;
    padding: 4px;
    text-align: center;
  }
  #kohana-profiler td {
    padding-right: 1em;
  }
  <? echo $styles ?>
</style>

<div id="kohana-profiler">
  <? foreach ($profiles as $profile): ?>
  <?= $profile->render(); ?>
  <? endforeach; ?>
  <p class="kp-meta"><?= t("Profiler executed in ") . number_format($execution_time, 3) ?>s</p>
</div>
