<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class Gallery_Formo extends Formo_Core_Formo {
  // Set our form status responses
  const NOT_SENT = 1;
  const PASSED = 2;
  const FAILED = 3;

  /**
   * Add a CSRF element into every form that uses Access::verify_csrf() for validation.
   *
   * @see  Formo::__construct()
   */
  public function __construct(array $array=null) {
    parent::__construct($array);

    switch ($this->get("driver")) {
      // If the driver is form (i.e. the parent form instead of a field within it), add the CSRF.
      // The "can_be_empty" argument means that, if the csrf field is empty in $_POST (i.e. illegal
      // access), go ahead and set the form entry as as null instead of passing along the pre-filled
      // value to validate (which would indicate legal access).
      case "form":
        $this->add("csrf", "input|hidden", Access::csrf_token());
        $this->csrf
          ->set("can_be_empty", true)
          ->add_rule("Access::verify_csrf", array(":value"));
        break;
      // For the rest of these cases, we're just auto-adding CSS classes based on the input type.
      // The CSS class names are chosen to be compatible with Gallery 3.0.x (which used Forge),
      // and can be overridden using Formo::remove_class("foo") or Formo::set("class", "foo").
      case "checkbox":
        $this->set("template", "field_template");  // We don't use a special checkbox template
        $this->add_class("checkbox");
        break;
      case "checkboxes":
        $this->add_class("checklist");
        break;
      case "radios":
        $this->add_class("radio");  // Note: the missing "s" isn't a typo.
        break;
      case "select":
        $this->add_class("dropdown");
        break;
      case "textarea":
        $this->add_class("textarea");
        break;
      case "input":
        switch ($this->attr("type")) {
          case "password":
            $this->add_class("password");
            break;
          case "submit":
            $this->add_class("submit");
            break;
          case "text":
          case null:
            $this->add_class("textbox");
            break;
        }
        break;
    }
  }

  /**
   * Overload Formo::validate() to set the form status.  We use this in Response::ajax_form().
   *
   * @see  Formo::validate()
   * @see  Response::ajax_form()
   */
  public function validate() {
    if (!$this->sent()) {
      $this->set("status", Formo::NOT_SENT);
      return false;
    } else {
      if ($result = parent::validate()) {
        $this->set("status", Formo::PASSED);
      } else {
        $this->set("status", Formo::FAILED);
      }
    }
    return $result;
  }

  /**
   * Overload Formo::add_rule() to allow us to define error messages inline.  We use this
   * approach instead of using Kohana message files.
   * @todo: consider recasting this as a patch to send upstream to the Formo project.
   *
   * @see  Formo::add_rule()
   */
  public function add_rule($rule, $params=null, $error_message=null) {
    // If add_rule() is called using an array, separate it into its parts for clarity.
    if (is_array($rule)) {
      return $this->add_rule($rule[0], Arr::get($rule, 1), Arr::get($rule, 2, $params));
    }

    if (isset($error_message)) {
      $error_messages = $this->get("error_messages", array());
      $error_messages[$rule] = $error_message;
      $this->set("error_messages", $error_messages);
    }

    return parent::add_rule($rule, $params);
  }

  /**
   * Overload Formo::close() to add our script data to the end of the form/group rendering.
   * This throws an exception if there's data applied to a non-parent form element.
   *
   * @see  Formo::close()
   */
  public function close() {
    $script_data = (string) $this->get("script_data");
    if ($script_data && !$this->driver("is_a_parent")) {
      throw new Gallery_Exception("Cannot add scripts to form elements");
    }

    return $script_data . parent::close();
  }

  /**
   * Add a script link to the end of this form/group.  The URL can be relative or absolute.
   * This can be accessed later using Formo::get("script_data") or Formo::set("script_data", ...).
   */
  public function add_script_url($url) {
    return $this->set("script_data", $this->get("script_data") . HTML::script($url) . "\n");
  }

  /**
   * Add script text to the end of this form/group.  The <script> tags are automatically added.
   * This can be accessed later using Formo::get("script_data") or Formo::set("script_data", ...).
   */
  public function add_script_text($text) {
    return $this->set("script_data", $this->get("script_data") .
      "<script type=\"text/javascript\">\n{$text}\n</script>\n");
  }

  /**
   * Return whether or not the field is hidden.  This is used when we render our custom templates.
   */
  public function is_hidden() {
    return ($this->attr("type") == "hidden");
  }

  /**
   * Add a field to a form just before its first submit button.  If no submit button is found,
   * the field is added to the end of the form.  We use this in events to add fields to pre-built
   * forms (e.g. tag, reCAPTCHA...).  The syntax is the same as Formo::add().
   *
   * @see Formo::add()
   */
  public function add_before_submit($alias, $driver=null, $value=null, array $opts=null) {
    return $this->_add_next_to_submit("before", $alias, $driver, $value, $opts);
  }

  /**
   * Add a field to a form just after its last submit button.  If no submit button is found,
   * the field is added to the end of the form.  We use this in events to add fields to pre-built
   * forms (e.g. tag, reCAPTCHA...).  The syntax is the same as Formo::add().
   *
   * @see Formo::add()
   */
  public function add_after_submit($alias, $driver=null, $value=null, array $opts=null) {
    return $this->_add_next_to_submit("after", $alias, $driver, $value, $opts);
  }

  /**
   * Process the add before/after submit functions.
   */
  protected function _add_next_to_submit($position, $alias, $driver, $value, $opts) {
    $types = Arr::flatten($this->as_array("attr.type", true));
    if ($position == "after") {
      $types = array_reverse($types);
    }

    foreach ($types as $submit_alias => $type) {
      if ($type != "submit") {
        continue;
      }

      // Found a submit button - add the field to its parent, then reorder to be next to the submit.
      $target = $this->find($submit_alias)->parent();
      $target->add($alias, $driver, $value, $opts);
      $target->order($alias, $position, $submit_alias);
      return $this;
    }

    // Couldn't find a submit button - add the field to the end of the form.
    $this->add($alias, $driver, $value, $opts);
    return $this;
  }

  /**
   * Merge two groups in a form.  This takes all elements from the source, adds them
   * to the target, then removes the target.  This could yield some odd results if
   * Formo namespacing is turned on (which it isn't in Gallery).
   *
   * @param string $source alias of source group
   * @param string $target alias of target group
   */
  public function merge_groups($source, $target) {
    foreach ($this->$source->as_array() as $field) {
      $this->$target->add($field);
    }
    $this->remove($source);
    return $this;
  }
}
