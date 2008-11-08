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
class Auth_Basic_Driver implements Auth_Driver {
  // Configuration
  protected $_config;

  /**
   * Instantiate the Driver and initialize it's configuration.
   */
  public function __construct($config = array()) {
    // Load config
    $config +=  Kohana::config('basic_auth');

    // Clean up the salt pattern and split it into an array
    $config['salt_pattern'] = preg_split('/,\s*/', $config['salt_pattern']);
    $this->_config = $config;

    Kohana::log('debug', 'Auth_Basic_Driver Library initialized');
  }

  /**
   * @see Auth_Driver::set_user_password
   *
   * @param int $user_id
   * @param string $password
   * @return void
   */
  public function set_user_password($user_id, $password_text) {
    $password = ORM::factory("password")->where('user_id', $user_id)->find();
    $password->password = $this->_hash_password($password_text);
    if (empty($password->user_id)) {
      $password->user_id = $user_id;
    }
    $password->save();
  }

  /**
   * Validates a user id password combination.
   *
   * @param   int   user_id
   * @param   string   password
   * @return  boolean
   */
  public function is_valid_password($user_id, $password_text) {
    $password = ORM::factory("password")
      ->where('user_id', $user_id)
      ->find();
    if ($password->loaded != true) {
      return false;
    }

    // Get the salt from the stored password
    $salt = $this->_find_salt($password->password);
    $hashed = $this->_hash_password($password_text, $salt);

    return $hashed === $password->password;
  }
  
  /**
   * Creates a hashed password from a plaintext password, inserting salt
   * based on the configured salt pattern.
   *
   * @param   string  plaintext password
   * @return  string  hashed password string
   */
  private function _hash_password($password, $salt = FALSE) {
    if ($salt === FALSE) {
      // Create a salt seed, same length as the number of offsets in the pattern
      $salt = substr($this->_hash(uniqid(NULL, TRUE)), 0, count($this->_config['salt_pattern']));
    }

    // Password hash that the salt will be inserted into
    $hash = $this->_hash($salt . $password);

    // Change salt to an array
    $salt = str_split($salt, 1);

    // Returned password
    $password = '';

    // Used to calculate the length of splits
    $last_offset = 0;

    foreach ($this->_config['salt_pattern'] as $offset) {
      // Split a new part of the hash off
      $part = substr($hash, 0, $offset - $last_offset);

      // Cut the current part out of the hash
      $hash = substr($hash, $offset - $last_offset);

      // Add the part to the password, appending the salt character
      $password .= $part . array_shift($salt);

      // Set the last offset to the current offset
      $last_offset = $offset;
    }

    // Return the password, with the remaining hash appended
    return $password . $hash;
  }

  /**
   * Perform a hash, using the configured method.
   *
   * @param   string   string to hash
   * @return  string
   */
  private function _hash($str) {
    return hash($this->_config['hash_method'], $str);
  }

  /**
   * Finds the salt from a password, based on the configured salt pattern.
   *
   * @param   string  hashed password
   * @return  string
   */
  private function _find_salt($password)   {
    $salt = '';

    foreach ($this->_config['salt_pattern'] as $i => $offset)     {
      // Find salt characters... take a good long look..
      $salt .= substr($password, $offset + $i, 1);
    }

    return $salt;
  }
}

