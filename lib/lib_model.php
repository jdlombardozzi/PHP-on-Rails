<?php
class Model
{
  public $controller_object;
  public $mysql;
  public $properties;
  public $errors = array();

  // def instantiate(record)
  // 	model = find_sti_class(record[inheritance_column]).allocate
  //       	model.init_with('attributes' => record)
  //       	model
  //     end

//  function __construct($controller_object)
//  {
//    $this->controller_object = $controller_object;
//    $this->mysql = Mysql::instance()->connection;
//  }

  public function __call($name, $args)
  {
    $name = strtolower($name);
    return isset($this->properties[$name]) ? $this->properties[$name] : call_user_func($name, $this);
  }

  public function __get($name)
  {
    $name = strtolower($name);
    return method_exists($this, $name) ? call_user_func(array(&$this, $name)) : $this->properties[$name];
  }

  public function __construct($properties)
  {
    $this->properties = $properties;
  }
}