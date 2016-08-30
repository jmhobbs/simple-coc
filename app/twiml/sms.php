<?php
  define('SAFE_ENTRY', true);
  require_once('../../bootstrap.php');

  use Twilio\Rest\Client;

  if($_REQUEST['AccountSid'] != App::config('twilio.sid') || $_REQUEST['secret'] != App::config('secret')) {
    App::NotFound();
  }

  $number = Number::findByNumber($_REQUEST['To']);

  if(is_null($number)) { 
    header('Content-Type: text/plain');
    die('An error occurred.');
  }

  // Handle replies from CoC contact number.
  if(clean_number($_REQUEST['From']) == $number->destination) {
    $matches = null;
    if(! preg_match('/^\[([0-9]+)\](.*)$/', $_REQUEST['Body'], $matches)) {
      header('Content-Type: text/plain');
      die('Invalid Reply');
    }

    $id = $matches[1];
    $body = trim($matches[2]);

    $reporter = Reporter::findById($id);
    if(is_null($reporter) or $reporter->number_id != $number->id) {
      header('Content-Type: text/plain');
      die('Invalid ID');
    }
    
    $message = new Message([
      'sid'         => $_REQUEST['MessageSid'],
      'number_id'   => $number->id,
      'reporter_id' => $reporter->id,
      'inbound'     => 0,
      'body'        => $body
    ]);
    if( ! $message->save() ) {
      App::InternalServerError('Database Error');
    }

    $client = new Client(App::config('twilio.sid'), App::config('twilio.token'));

    $client->messages->create(
      $reporter->number,
      array(
        'from' => $number->number,
        'body' => $body
      )
    );

    die();
  }

  $reporter = Reporter::findByNumberIdAndFromNumber($number->id, $_REQUEST['From']);
  if(is_null($reporter)) {
    $reporter = new Reporter([
      'number'    => $_REQUEST['From'],
      'number_id' => $number->id,
    ]);
    if( ! $reporter->save() ) {
      App::InternalServerError('Database Error');
    }
  }

  $message = new Message([
    'sid'         => $_REQUEST['MessageSid'],
    'number_id'   => $number->id,
    'reporter_id' => $reporter->id,
    'inbound'     => true,
    'body'        => $_REQUEST['Body']
  ]);
  if( ! $message->save() ) {
    App::InternalServerError('Database Error');
  }

  $data = [
    'destination_number' => $number->destination,
    'reporter_id' => $reporter->id,
    'message_body' => $_REQUEST['Body'],
    'link' => url('/report/?' . http_build_query([
      'secret' => App::config('secret'),
      'token'  => $reporter->token,
      'id'     => $reporter->id
    ]))
  ];

  App::OK('twiml/sms.xml', $data, 'text/xml');
