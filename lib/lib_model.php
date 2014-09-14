<?php
// TODO: Add change tracking, something like this: https://github.com/rails/rails/blob/b8302bcfdaec2a9e7658262d6feeb535c572922d/activemodel/lib/active_model/dirty.rb#L148
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

    if(method_exists($this, $name)) {
      return call_user_func(array(&$this, $name));
    }

    if($this->columns && !in_array($name, $this->columns)) {
      throw new BadMethodCallException('undefined method `'.$name.'` for '.get_class($this));
    }

    return $this->properties[$name];
  }

  public function __set($name, $value) {
    if(!in_array($name, $this->columns)) {
      throw new BadMethodCallException('undefined method `'.$name.'` for '.get_class($this));
    }

    $this->properties[$name] = $value;
    $this->properties['updated_at'] = time();
  }

  public function __construct($properties)
  {
    $this->properties = $properties;
  }

  // Mass assign and save attributes from input
  public function update_attributes($attributes) {
    foreach($attributes as $k => $v) {
      $this->$k = $v;
    }

    return $this->save();
  }
}