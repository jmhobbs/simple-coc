<?php

  if(!defined('SAFE_ENTRY')) {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
    exit;
  }

  date_default_timezone_set('America/Chicago');

  require_once(__DIR__ . '/vendor/autoload.php');
  require_once(__DIR__ . '/app.php');
  App::init(__DIR__);

  

  require_once(__DIR__ . '/utils.php');
  require_once(__DIR__ . '/models.php');
