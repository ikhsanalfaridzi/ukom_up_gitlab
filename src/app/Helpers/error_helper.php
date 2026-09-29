<?php

/**
 * Custom Error Page For Codeigniter 4
 *
 * @author 		  : Kevin Kurnia Wikarta
 * @copyright   : Copyright (c) 2025
 * @link 		    : https://kevkw.id
 * @version 	  : 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	  : 2025-02-02
 */

if (! function_exists('show_error')) {
  /**
   * Show Error Base On Status Code
   * 
   * @param string $message Pesan error yang akan ditampilkan.
   * @param int $status_code Kode status HTTP (default: 500).
   * @param string $heading Judul error (default: 'An Error Was Encountered').
   * 
   * @return CodeIgniter\View\View|string
   */
  function show_error($message, $status_code = 500, $heading = 'An Error Was Encountered')
  {
    $status_code = is_numeric($status_code) ? abs($status_code) : 500;
    $exit_status = ($status_code < 100) ? ($status_code + 9) : 1; // 9 EXIT__AUTO_MIN

    switch ($status_code) {
      case 403:
        $view = 'errors/html/error_403';
        break;
      case 401:
        $view = 'errors/html/error_401';
        break;
      case 404:
        $view = 'errors/html/error_404';
        break;
      default:
        $view = 'errors/html/error_general';
    }

    return view($view, compact('message', 'status_code', 'heading'));
  }
}
