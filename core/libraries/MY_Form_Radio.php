<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

/** * FORGE radio button input library. * */
/* http://forum.kohanaphp.com/comments.php?DiscussionID=164 */
class Form_Radio extends Form_Input {
  protected $data = array (
    'name' => '',
    'class' => 'radio',
    'type'=>'radio',
    'options'=>array()
  );
  protected $protect = array('type');

  public function __get($key) {
    if ($key == 'value') {
      return $this->selected;
    }
    return parent::__get($key);
  }

  public function html_element() {
    // Import base data
    $data = $this->data;
    // Get the options and default selection
    $options = arr::remove('options', $data);
    $selected = arr::remove('selected', $data);
    // martin hack
    unset($data['label']);
    $html ='';
    foreach($options as $option=>$labelText){
      $html .= form::radio(array ('name' => $data['name'], 'id' => $data['name'] . "_" . $option ),
        $option, $this->value? $this->value==$option: $data['default']==$option).
        form::label($data['name']."_".$option , $labelText)." ";
    }
    return $html;
  }

  protected function load_value() {
    if (is_bool($this->valid))
      return;
    $this->data['selected'] = $this->input_value($this->name);
  }
} // End Form radio
