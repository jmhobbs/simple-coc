<?php

  if(!defined('SAFE_ENTRY')) {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
    exit;
  }

  function arr_get($array, $key, $default=null) {
    if(array_key_exists($key, $array)) {
      return $array[$key];
    }
    return $default;
  }

  function url($path) {
    return 'http' . ((! empty($_SERVER['HTTPS'])) ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'] . $path;
  }

  function clean_number($number) {
    return preg_replace('/[^0-9]/', '', $number);
  }
