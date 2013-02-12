<?php
class Controller
{
  public $variables = array('errors' => array());
  public $application;

  // Controller initalizer will store a reference to the application
  function __construct($application)
  {
    $this->application = &$application;
  }

  // Any property set within an extended Controller's methods will be added to $this->variables
  public function __set($name, $value)
  {
    $this->variables[$name] = $value;
  }

	// Any property accessed within an extended Controller's methods or coordinating views will go through this getter
	// It will first check if the model has this property, else it will check $this->variables
  public function __get($name)
  {
    # Check if we have
//    print $name;
//    if (property_exists($this->application->model_objects, $name)) {
//      return $this->application->model_objects->$name;
//    }

    return $this->variables[$name];
  }

  // Any Controller function call will be run this call method
  public function __call($name, $arguments)
  {
    # Check if method exists and raise exception if it doesn't.
    if (!method_exists($this, $name)){
      throw new BadMethodCallException(get_class($this).'#'.$name.' does not exist  ');
    }

    return call_user_func_array(array(&$this, $name), $arguments);
  }
}