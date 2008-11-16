<?php
class Forge extends Forge_Core {
  public function render($template="form.html", $custom=false) {
    return parent::render($template, $custom);
  }

  /**
   * Associate validation rules defined in the model with this form.
   */
  public function add_rules_from($model) {
    foreach ($this->inputs as $name => $input) {
      if (isset($input->inputs)) {
        $input->add_rules_from($model);
      }
      if (isset($model->rules[$name])) {
        $input->rules($model->rules[$name]);
      }
    }
  }
}