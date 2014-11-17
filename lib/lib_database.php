<?php
class Database {
  protected static $instance;
  public $connection;
  protected $info;

  public function __construct() {
    require BASEDIR."config/database.php";
    $this->info = $info;

    $this->connect();
  }

  public static function instance() {
    if(!isset(self::$instance)) {
      $c = __CLASS__;
      self::$instance = new $c;
    }

    return self::$instance;
  }

  public function __clone() {
    trigger_error('Clone is not allowed.', E_USER_ERROR);
  }

  public function prefix() {
    return $this->info['prefix'];
  }
}

class dbMysql extends Database{
  private function connect() {
    // Initiate mysql connection.
    $connection = mysql_connect($this->info['host'].':'.$this->info['port'], $this->info['user'], $this->info['password']);

    if(!$connection) {
      die('Could not connect: '.mysql_error());
    }

    // Set current database
    if(!mysql_select_db($this->info['database'], $connection)) {
      die ("Can't use {$this->info['prefix']}{$this->info['database']} : ".mysql_error());
    }

    if(array_key_exists('charset', $this->info)) {
      mysql_set_charset($this->info['charset'], $connection);
    }

    $this->connection = $connection;
  }
}
class dbMysqli extends Database {
  private function connect() {
    // Initiate connection.
    $connection = mysqli_connect($this->info['host'] . ':' . $this->info['port'], $this->info['user'], $this->info['password']);

    if(!$connection) {
      die('Could not connect: ' . mysqli_connect_error());
    }

    // Set current database
    if(!mysqli_select_db($this->info['database'], $connection)) {
      die ("Can't use {$this->info['prefix']}{$this->info['database']} : ".mysqli_error());
    }

    if(array_key_exists('charset', $this->info)) {
      mysqli_set_charset($this->info['charset'], $connection);
    }

    $this->connection = $connection;
  }
}