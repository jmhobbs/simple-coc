<?php

  class App {

    public static $config = [];
    public static $twig = null;
    public static $mysqli = null;
    public static $error_caught = false;

    public static function init($root) {
      self::$config = require_once(__DIR__ . '/config.php');

      $loader = new Twig_Loader_Filesystem($root . '/views');

      $twig_options = [
        'cache' => (true == self::config('twig.cache', false)) ? $root . '/cache' : false
      ];
      
      self::$twig = new Twig_Environment($loader, $twig_options);

      set_error_handler('App::HandlePHPError');

      self::$mysqli = new mysqli(
        self::config('mysql.hostname'),
        self::config('mysql.username'),
        self::config('mysql.password'),
        self::config('mysql.database')
      );

      if (self::$mysqli->connect_error) {
        self::InternalServerError('Database Error');
      }
    }

    public static function config($key, $default=null) {
      $path = explode('.', $key);
      $value = self::$config;
      foreach($path as $key) {
        if(array_key_exists($key, $value)) {
          $value = $value[$key];
        }
        else {
          return $default;
        }
      }
      return $value;
    }

    public static function InternalServerError($message) {
      header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error", true, 500);
      $template = self::$twig->loadTemplate('500.html');
      die($template->render(['message' => $message]));
    }
    
    public static function NotFound() {
      header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
      $template = self::$twig->loadTemplate('404.html');
      die($template->render([]));
    }

    public static function OK($template, $data, $content_type='text/html') {
      header('Content-Type: ' . $content_type);
      $template = self::$twig->loadTemplate($template);
      die($template->render($data));
    }

    public static function HandlePHPError ($errno, $errstr, $errfile, $errline, $errcontext) {
      if (!(error_reporting() & $errno)) { return; }

      error_log("$errfile [$errline] : $errstr\n" . print_r($errcontext, true));

      if(self::$error_caught) {
        header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error", true, 500);
        die('Internal Server Error');
      }
      self::$error_caught = true;
      self::InternalServerError($errstr);
      return true;
    } 
  }
