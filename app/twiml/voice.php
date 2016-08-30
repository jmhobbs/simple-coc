<?php
  define('SAFE_ENTRY', true);
  require_once('../../bootstrap.php');

  if($_REQUEST['AccountSid'] != App::config('twilio.sid') || $_REQUEST['secret'] != App::config('secret')) {
    App::NotFound();
  }

  $number = Number::findByNumber($_REQUEST['To']);
  if(is_null($number)) { 
    header('Content-Type: text/plain');
    die('An error occurred.');
  }

  $call = new Call([
    'number'      => $_REQUEST['From'],
    'number_id'   => $number->id,
  ]);
  if( ! $call->save() ) {
    App::InternalServerError('Database Error');
  }

  $data = [
    'caller_id' => $_REQUEST['To'],
    'number' => $number
  ];

  App::OK('twiml/voice.xml', $data, 'text/xml');
