<?php
  define('SAFE_ENTRY', true);
  require_once('../../bootstrap.php');

  if($_REQUEST['secret'] != App::config('secret')) {
    App::NotFound();
  }

  use Twilio\Rest\Client;

  $id = arr_get($_REQUEST, 'id');
  $token = arr_get($_REQUEST, 'token');

  $reporter = Reporter::findById($id);
  if(is_null($reporter) or $reporter->token !== $token) {
    App::NotFound();
  }

  if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $number = Number::findById($reporter->number_id);

    $message = new Message([
      'sid'         => '',
      'number_id'   => $reporter->number_id,
      'reporter_id' => $reporter->id,
      'inbound'     => 0,
      'body'        => $_POST['body']
    ]);
    if( ! $message->save() ) {
      App::InternalServerError('Database Error');
    }

    $client = new Client(App::config('twilio.sid'), App::config('twilio.token'));

    $client->messages->create(
      $reporter->number,
      array(
        'from' => $number->number,
        'body' => $message->body
      )
    );
  }

  $messages = Message::findByReporterId($reporter->id);

  App::OK('report.html', ['reporter' => $reporter, 'messages' => $messages]);
