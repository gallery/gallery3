<?php defined("SYSPATH") or die("No direct script access.");/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class rest_Core {
  /**
   * Authorization Failure
   */
  static function forbidden($log_message=null) {
    return self::_format_response(t("Authorization failed"), $log_message);
  }

  /**
   * Invalid Failure
   */
  static function invalid_request($log_message=null) {
    return self::_format_response(t("Invalid request"), $log_message);
  }

  /**
   * Not Implemented
   */
  static function not_implemented($log_message=null) {
    return self::_format_response(t("Service not implemented"), $log_message);
  }

  /**
   * Internal Error
   */
  static function internal_error($log_message=null) {
    return self::_format_response(t("Internal error"), $log_message);
  }

  /**
   * Resource Not Found
   */
  static function not_found($log_message=null) {
    return self::_format_response(t("Internal error"), $log_message);
  }

  /**
   * Success
   */
  static function success($response_data, $message=null) {
    $response = array("status" => "OK");
    if (!empty($message)) {
      $response["message"] = (string)$message;
    }
    if ($response_data) {
      $response = array_merge($response, $response_data);
    }
    // We don't need to save the session for this request
    Session::abort_save();
    return json_encode($response);
  }

  private static function _format_response($message, $log_message) {
    if (!empty($log_message)) {
      Kohana::log("info", $log_message);
    }
    // We don't need to save the session for this request
    Session::abort_save();
    return json_encode(array("status" => "ERROR", "message" => (string)$message));
  }
}
