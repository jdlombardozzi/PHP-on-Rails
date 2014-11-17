<?php
class Messages
{
  private $current = array();
  private $keep = array();

  public function __construct()
  {
    if (isset($_SESSION['flash'])) {
      foreach ($_SESSION['flash'] as $k => $v) {
        $this->current[$k] = $v;
      }
    }
  }

  public function __destruct()
  {
    foreach ($this->current as $k => $v) {
      if (array_key_exists($k, $this->keep) && $this->keep[$k] == $v) {
        // keep flash
        $_SESSION['flash'][$k] = $v;

      } else {
        // delete flash
        unset($_SESSION['flash'][$k]);
        unset($this->current[$k]);
        unset($this->keep[$k]);
      }
    }
  }

  public function __get($key) {
    if (array_key_exists($key, $this->current)) return $this->current[$key];

    return null;
  }

  public function __set($key, $value)
  {
    $_SESSION['flash'][$key] = $value;
  }

  public function keep($key)
  {
    $this->keep[$key] = $this->__get($key);
  }

  public function messages() {
    return $this->current();
  }
}