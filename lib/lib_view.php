<?php
class View
{
  public $controller_object;
  public $view_flow = array();

  // Initializer will store reference to controller object
  public function __construct($controller_object)
  {
    $this->controller_object = $controller_object;
  }

  // Renders the view template
  public function render($script)
  {
    ob_start();
    $this->_include($script);
    return ob_get_clean();
  }

  // Make all variables in controller available to view
  public function __get($key)
  {
    return (isset($this->$key) ? $this->$key : null);
  }

  protected function _include()
  {
    # Add variable to view template
    if ($this->controller_object->variables != null) {
      foreach ($this->controller_object->variables as $name => $value) {
        ${$name} = $value;
      }
    }

    $template = func_get_arg(0);

    if(!file_exists(BASEDIR.'app/views/'.func_get_arg(0))){
      throw new Exception("{$template} view does not exist", 404);
    };

    # TODO: Mime recognition, absolute/relative paths,
    include BASEDIR.'app/views/'.func_get_arg(0);
  }

  public function content_for($name, $content)
  {
    $this->view_flow[$name] = $content;
  }

  public function yield($name)
  {
    if (array_key_exists($name, $this->view_flow)) {
      print $this->view_flow[$name];
      return;
    }
  }
}