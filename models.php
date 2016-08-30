<?php

  if(!defined('SAFE_ENTRY')) {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
    exit;
  }

  class Model {
    protected static $_tablename = null;
    
    public function __construct($values=[]) {
      $this->values($values);
    }

    public function save() {
      if(is_null($this->id)) {
        return $this->create();
      }
      else {
        return $this->update();
      }
    }
    
    public function values($values=[]) {
      foreach($values as $key => $value) {
        $this->{$key} = $value;
      }
    }

    public static function findById($id) {
      $klass = get_called_class();
      $result = App::$mysqli->query("SELECT * FROM `" . $klass::$_tablename . "` WHERE `id` = '" . App::$mysqli->real_escape_string($id) ."' LIMIT 1");
      if ($result and $result->num_rows == 1) {
        $model = new self($result->fetch_assoc());
        $result->close();
        return $model;
      }
      return null;
    }
  }

  class Number extends Model{
    protected static $_tablename = 'numbers';

    public $id = null;
    public $number = null;
    public $destination = null;

    
    public static function findByNumber($number) {
      $cleaned = clean_number($number);
      $result = App::$mysqli->query("SELECT * FROM `" . self::$_tablename . "` WHERE `number` = '" . App::$mysqli->real_escape_string($cleaned) ."' LIMIT 1");
      if ($result and $result->num_rows == 1) {
        $model = new Number($result->fetch_assoc());
        $result->free();
        return $model;
      }
      return null;
    }
  }

  class Reporter extends Model {
    protected static $_tablename = 'reporters';

    public $id        = null;
    public $number    = null;
    public $number_id = null;
    public $token     = null;
    public $created   = null;

    public static function findByNumberIdAndFromNumber($number_id, $from) {
      $cleaned = clean_number($from);
      $result = App::$mysqli->query("
SELECT *
FROM `" . self::$_tablename . "`
WHERE
  `number` = '" . App::$mysqli->real_escape_string($cleaned) ."'
AND
  `number_id` = '" . App::$mysqli->real_escape_string($number_id) ."'
LIMIT 1
      ");
      if ($result and $result->num_rows == 1) {
        $model = new Reporter($result->fetch_assoc());
        $result->free();
        return $model;
      }
      return null;
    }

    public function create () {

      if(empty($this->token)) {
        $this->token = substr(uniqid() . uniqid(), 0, 16);
      }

      $this->number = clean_number($this->number);

      $sql = "
INSERT INTO `" . self::$_tablename . "`
  (
    `id`,
    `number`,
    `number_id`,
    `token`,
    `created`
  )
VALUES
  (
    NULL,
    '" . App::$mysqli->real_escape_string($this->number) ."',
    '" . App::$mysqli->real_escape_string($this->number_id) ."',
    '" . App::$mysqli->real_escape_string($this->token) ."',
    '" . App::$mysqli->real_escape_string(time()) ."'
  )
";

      if (true != App::$mysqli->query($sql)) {
        error_log(App::$mysqli->error);
        return false;
      }

      $this->id = App::$mysqli->insert_id;
      return true;
    }

    public function update () {

      $this->number = clean_number($this->number);

      $sql = "
UPDATE `" . self::$_tablename . "`
SET
  `number` = '" . App::$mysqli->real_escape_string($this->number) ."',
  `number_id` = '" . App::$mysqli->real_escape_string($this->number_id) ."',
  `token` = '" . App::$mysqli->real_escape_string($this->token) ."'
 WHERE
  `id` = '" . App::$mysqli->real_escape_string($this->id) ."'
 ";

      if (true != App::$mysqli->query($sql)) {
        error_log(App::$mysqli->error);
        return false;
      }

      return true;
    }
  }

  class Message extends Model {
    protected static $_tablename = 'messages';

    public $id          = null;
    public $sid         = null;
    public $number_id   = null;
    public $reporter_id = null;
    public $inbound     = null;
    public $body        = null;
    public $created     = null;

    public function create () {

      if(empty($this->token)) {
        $this->token = substr(uniqid() . uniqid(), 0, 16);
      }

      $sql = "
INSERT INTO `" . self::$_tablename . "`
  (
    `id`,
    `sid`,
    `number_id`,
    `reporter_id`,
    `inbound`,
    `body`,
    `created`
  )
VALUES
  (
    NULL,
    '" . App::$mysqli->real_escape_string($this->sid) ."',
    '" . App::$mysqli->real_escape_string($this->number_id) ."',
    '" . App::$mysqli->real_escape_string($this->reporter_id) ."',
    '" . App::$mysqli->real_escape_string($this->inbound) ."',
    '" . App::$mysqli->real_escape_string($this->body) ."',
    '" . App::$mysqli->real_escape_string(time()) ."'
  )
";

      if (true != App::$mysqli->query($sql)) {
        error_log(App::$mysqli->error);
        return false;
      }

      $this->id = App::$mysqli->insert_id;
      return true;
    }

    public function update () {

      $sql = "
UPDATE `" . self::$_tablename . "`
SET
  `sid` = '" . App::$mysqli->real_escape_string($this->sid) ."',
  `number_id` = '" . App::$mysqli->real_escape_string($this->number_id) ."',
  `reporter_id` = '" . App::$mysqli->real_escape_string($this->reporter_id) ."',
  `inbound` = '" . App::$mysqli->real_escape_string($this->inbound) ."',
  `body` = '" . App::$mysqli->real_escape_string($this->inbound) ."'
WHERE
  `id` = '" . App::$mysqli->real_escape_string($this->id) ."'
";

      if (true != App::$mysqli->query($sql)) {
        error_log(App::$mysqli->error);
        return false;
      }

      return true;
    }

    public static function findByReporterId ($reporter_id) {
      $result = App::$mysqli->query("
SELECT *
FROM `" . self::$_tablename . "`
WHERE
  `reporter_id` = '" . App::$mysqli->real_escape_string($reporter_id) ."'
ORDER BY `id` DESC
      ");
      if ($result) {
        $messages = [];
        while($row = $result->fetch_assoc()) {
          array_push($messages, new Reporter($row));
        }
        $result->free();
        return $messages;
      }
      return null;
    }
  }

  class Call extends Model {
    protected static $_tablename = 'calls';

    public $id        = null;
    public $number    = null;
    public $number_id = null;
    public $created   = null;

    public function create () {

      $this->number = clean_number($this->number);

      $sql = "
INSERT INTO `" . self::$_tablename . "`
  (
    `id`,
    `number`,
    `number_id`,
    `created`
  )
VALUES
  (
    NULL,
    '" . App::$mysqli->real_escape_string($this->number) ."',
    '" . App::$mysqli->real_escape_string($this->number_id) ."',
    '" . App::$mysqli->real_escape_string(time()) ."'
  )
";

      if (true != App::$mysqli->query($sql)) {
        error_log(App::$mysqli->error);
        return false;
      }

      $this->id = App::$mysqli->insert_id;
      return true;
    }

    public function update () {

      $this->number = clean_number($this->number);

      $sql = "
UPDATE `" . self::$_tablename . "`
SET
  `number` = '" . App::$mysqli->real_escape_string($this->number) ."',
  `number_id` = '" . App::$mysqli->real_escape_string($this->number_id) ."',
 WHERE
  `id` = '" . App::$mysqli->real_escape_string($this->id) ."'
 ";

      if (true != App::$mysqli->query($sql)) {
        error_log(App::$mysqli->error);
        return false;
      }

      return true;
    }
  }
