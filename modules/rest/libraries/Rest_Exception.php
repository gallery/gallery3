<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Creates a "Page Not Found" exception.
 *
 * $Id: Kohana_404_Exception.php 4679 2009-11-10 01:45:52Z isaiah $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

class Rest_Exception_Core extends Exception {
  /**
   * Set internal properties.
   */
  public function __construct($code, $text) {
    parent::__construct("$code $text");
  }

  /**
   * Throws a new Rest exception.
   *
   * @throws  Rest_Exception
   * @return  void
   */
  public static function trigger($code, $text, $log_message=null) {
    $message = "$code: $text" . (!empty($log_message) ? "\n$log_message" : "");
    Kohana_Log::add("info", $message);
    throw new Rest_Exception($code, $text);
  }

  /**
   * Sends the headers, to emulate server behavior.
   *
   * @return void
   */
  public function sendHeaders() {
    header('HTTP/1.1 {$this->getMessage()}');
  }
} // End Rest Exception