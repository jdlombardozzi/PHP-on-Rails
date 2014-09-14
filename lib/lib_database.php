<?php
class Mysql {
  private static $instance;
  public $connection;
  private $info;

  public function __construct() {
    require BASEDIR."config/database.php";
    $this->info = $info;

    $this->connect();
  }

  private function connect() {
    // Initiate mysql connection.
    $mysql = mysql_connect($this->info['host'].':'.$this->info['port'], $this->info['user'], $this->info['password']);

    if(!$mysql) {
      die('Could not connect: '.mysql_error());
    }

    // Set current database
    if(!mysql_select_db($this->info['database'], $mysql)) {
      die ("Can't use {$this->info['prefix']}{$this->info['database']} : ".mysql_error());
    }

    if($this->info['charset']) {
      mysql_set_charset($this->info['charset'], $mysql);
    }

    $this->connection = $mysql;
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