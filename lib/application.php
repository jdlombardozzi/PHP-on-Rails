<?php

// Front Controller
class Application {
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
  function __construct() {
    $this->initialized = false;
    $this->request_verb = isset($_POST['_method']) && in_array(strtoupper($_POST['_method']), array('PUT', 'DELETE')) ? strtoupper($_POST['_method']) : $_SERVER['REQUEST_METHOD'];
  }

  protected function error($nr, $message) {
    $http_codes = array(404 => 'Not Found',
                        500 => 'Internal Server Error');

    header($_SERVER['SERVER_PROTOCOL']." $nr {$http_codes[$nr]}");
    echo "
      <style type='text/css'>
        .routing-error { font-family:helvetica,arial,sans; border-radius:10px; border:1px solid #ccc; background:#efefef; padding:20px; }
        .routing-error h1 { padding:0px; margin:0px 0px 20px; line-height:1; }
        .routing-error p { color:#444; padding:0px; margin:0px; }
      </style>
      <div class='error routing-error'>
        <h1>Error $nr</h1>
        <p>$message</p>
      </div>";
    exit;
  }

  /**
   * Loads controller determined from translated route
   */
  private function _load_controller() {
    // Routes
    require(BASEDIR.'config/routes.php');
    $router->match_routes();

    if($router->route_found) {
      require_once(BASEDIR.'lib/lib_controller.php');
      require BASEDIR."app/controllers/{$router->module_path}{$router->controller}_controller.php";

      $class_name = ($router->module ? ucfirst($router->module).'__' : '').ucwords(preg_replace_callback('/_[a-z]{1}/', function ($matches) { return ucfirst(substr($matches[0], 1)); }, $router->controller)).'Controller';

      // Ensure class exists
      if(class_exists($class_name)) {
        $this->controller_object = new $class_name($this, $router);
        $this->controller_object->base_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/'.(substr(dirname($_SERVER['SCRIPT_NAME']), 1) ? substr(dirname($_SERVER['SCRIPT_NAME']), 1).'/' : '');

        if(method_exists($this->controller_object, $router->action)) {
          // ... and the action as well! Now, we have to figure out
          //     how we need to call this method:

          // iterate this method's parameters and compare them with the parameter names
          // we defined in the route. Then, reassemble the values from the URL and put
          // them in the same order as method's argument list.
          $m = new ReflectionMethod($this->controller_object, $router->action);
          $params = $m->getParameters();
          $args = array();
          foreach($params as $i => $p) {
            if(isset($router->params[$p->name])) {
              $args[$i] = urldecode($router->params[$p->name]);
            } else {
              // we couldn't find this parameter in the URL! Set it to 'null' to indicate this.
              $args[$i] = null;
            }
          }
        } else {
          $this->error(404, "Action ".$class_name.".".$router->action."() not found");
        }
      } else {
        $this->error(404, "No such controller: ".$class_name);
      }
    } else {
      $this->error(404, "Page not found");
    }
  }

  /**
   * Loads a view object coordinating to translated route
   */
  private function _load_view() {
    require BASEDIR.'lib/lib_view.php';
    $this->view_object = new View($this->controller_object);

    $this->view_object->content_for('layout', $this->view_object->render("{$this->controller_object->router->module_path}{$this->controller_object->router->controller}/{$this->controller_object->router->action}.phtml"));
  }

  /**
   * Loads and instantiate all models in the expected model directory
   */
  private function _load_models() {
    require_once(BASEDIR.'lib/lib_model.php');
    // Open model directory for reading
    if($handle = opendir(BASEDIR.'app/models')) {
      // Read each file in the model directory
      while(false !== ($file = readdir($handle))) {
        // Do a regex match on filename to be sure it is a php file
        if(preg_match('/[a-z]+\.php$/', $file)) {
          require_once(BASEDIR."app/models/{$file}");
        }
      }
    }
  }

  //  TODO: Run initializers out of the config/initializers directory
//  private function _run_initializers() {
//    return this;
//  }

  /**
   * Main method to begin execution of the application
   */
  public function run() {
//    if($this->initialized) throw new Exception('Application has already been initialized.');
//    $this->initialized = true;

    require BASEDIR.'lib/lib_database.php';

    // Load the model
    $this->_load_models();

    // Load the controller
    $this->_load_controller();

    // TODO: Check for filters

    // Run routed action
    $this->controller_object->{$this->controller_object->router->action}();

    // Load the view, render a layout view and the action view
    $this->_load_view();

    // Render and output the display
    echo $this->view_object->render("{$this->controller_object->router->module_path}layouts/application.phtml");
    exit;
  }
}
