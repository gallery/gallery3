<?php
class Forge extends Forge_Core {
  public function render($template="form.html", $custom=false) {
    return parent::render($template, $custom);
  }
}