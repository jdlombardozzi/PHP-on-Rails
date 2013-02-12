<?php
class Application
{
  private $routes;
  private $request_verb;
  private $controller;
  private $action;

  private $controller_object;
  private $view_object;
  public $model_objects = array();

  /**
   * Constructor to the entire appliciation
   * @param string $routes Contains the routes configuration to the application
   */
  function __construct($routes)
  {
    $this->routes = $routes;
    $this->request_verb = isset($_POST['_method']) && in_array(strtoupper($_POST['_method']), array('PUT', 'DELETE')) ? strtoupper($_POST['_method']) : $_SERVER['REQUEST_METHOD'];
  }

  /**
   * Will parse request variables and determine route to controller and action
   */
  private function _translate_route()
  {
    $this->controller = isset($_GET['controller']) && $_GET['controller'] != '' ? $_GET['controller'] : 'home';
    $this->action = isset($_GET['action']) && $_GET['action'] != '' ? $_GET['action'] : 'index';

    // Check root route
    if ($this->controller == null && $this->action == null) {
      if (isset($this->routes['root']) && preg_match('/^[a-z]+#[a-z]+$/', $this->routes['root'])) {
        try {
          list($this->controller, $this->action) = explode('#', $this->routes['root'], 2);
        } catch (Exception $e) {
          echo 'Caught exception: ', $e->getMessage(), "\n";
        }
      } else {
        throw new Exception('No valid route set.');
      }
    } else if ($this->controller != null && $this->action == null) {
      $this->action = 'show';
    }
  }

  /**
   * Loads controller determined from translated route
   */
  private function _load_controller()
  {
    require_once(BASEDIR.'lib/lib_controller.php');
    require BASEDIR."app/controllers/{$this->controller}_controller.php";
    $class_name = ucfirst($this->controller).'Controller';

		// If class doesn't exist issue 404
		if ( !class_exists($class_name) ) {
			print "404";
			exit;
		}

    $this->controller_object = new $class_name($this);

    $this->controller_object->base_url = (strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === FALSE ? 'http' : 'https').'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/';
  }

  /**
   * Loads a view object coordinating to translated route
   */
  private function _load_view()
  {
    require BASEDIR.'lib/lib_view.php';
    $this->view_object = new View($this->controller_object);

    $this->view_object->content_for('layout', $this->view_object->render("{$this->controller}/{$this->action}.phtml"));

    unset($controller_view);
  }

  /**
   * Loads and instantiate all models in the expected model directory
   */
  private function _load_models()
  {
    require_once(BASEDIR.'lib/lib_model.php');
    // Open model directory for reading
    if ($handle = opendir(BASEDIR.'app/models')) {
      // Read each file in the model directory
      while (false !== ($file = readdir($handle))) {
        // Do a regex match on filename to be sure it is a php file
        if (preg_match('/[a-z]+\.php$/', $file)) {
          require_once(BASEDIR."app/models/{$file}");
        }
      }
    }
  }

  /**
   * Main method to begin execution of the application
   */
  public function run()
  {
    $this->_translate_route();

    require BASEDIR.'lib/lib_database.php';

    // Load the model
    $this->_load_models();

    // Load the controller and run routed action
    $this->_load_controller();
    $this->controller_object->{$this->action}();

    // Load the view, render a layout view and the action view
    $this->_load_view();

    // Render and output the display
    echo $this->view_object->render('layouts/application.phtml');
    exit;
  }
}