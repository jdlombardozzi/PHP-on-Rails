<?php

class Router {
  const default_controller = 'home';
  const default_action = 'index';

  public $request_uri;
  public $routes;
  public $controller;
  public $action, $id;
  public $module;
  public $module_path;
  public $params;
  public $route_found = false;

  public function __construct() {
    $request = $this->get_request();

    $this->request_uri = $request;
    $this->routes = array();
  }

  public function get_request() {
    $request_uri = rtrim($_SERVER["REQUEST_URI"], '/');

    // find out the absolute path to this script
    $here = realpath(rtrim(dirname($_SERVER["SCRIPT_FILENAME"]), '/'));
    $here = str_replace("\\", "/", $here."/");

    // find out the absolute path to the document root
    $document_root = str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"])."/");

    // let's see if we can return a path that is expressed *relative* to the script
    // (i.e. if this script is in '/sites/something/router.php', and we are
    // requesting /sites/something/here/is/my/path.png, then this function will
    // return 'here/is/my/path.png')
    if (strpos($here, $document_root) !== false) {
      $relative_path = rtrim("/".str_replace($document_root, '', $here), '/');
      $path_route = urldecode(str_replace($relative_path, '', $request_uri));
      return trim($path_route, '/');
    }

    // nope - we couldn't get the relative path... too bad! Return the absolute path
    // instead.
    return urldecode($request_uri);
  }

  ## conditions:
  ### As: (string) Route name
  ### Defaults: (array) Set parameter defaults
  ### Via: (string) HTTP verb constraints
  ### Constraints: (array) Enforcing a format for parameters
  public function map($rule, $target = array(), $conditions = array()) {
    // Skip this mapping if the rule is already handled.
    // This ensures the first matching route principle
    if (isset($this->routes[$rule])) {
      return;
    }

    if (is_string($target)) {
      // Shorthand notation "controller#action"
      list($controller, $action) = explode('#', $target);
      $target = array('controller' => $controller, 'action' => $action);

      // Check for module
      if ($position = strrpos($controller, '__')) {
        $target['module'] = substr($controller, 0, $position);
        $target['controller'] = substr($controller, $position + 2);
      }
    }

    $this->routes[$rule] = new Route($rule, $this->request_uri, $target, $conditions);
  }

  public function render($arguments) {
  }

  public function default_routes() {
    $this->map(':controller');
    $this->map(':controller/:action');
    $this->map(':controller/:action/:id');
  }

  private function set_route($route) {
    $this->route_found = true;
    $params = $route->params;

    if (isset($params['controller'])) {
      $this->controller = $params['controller'];
      unset($params['controller']);
    }

    if (isset($params['action'])) {
      $this->action = $params['action'];
      unset($params['action']);
    }

    if (isset($params['module'])) {
      $this->module = $params['module'];
      unset($params['module']);

      $this->module_path = str_replace('__', '/', $this->module).'/';
    }

    $this->id = array_key_exists('id', $params) ? $params['id'] : null;
    $this->params = array_merge($params, $_GET);

    # Set default controller, if not provided.
    if (empty($this->controller)) {
      $this->controller = self::default_controller;
    }
    # Set default action, if not provided.
    if (empty($this->action)) {
      $this->action = self::default_action;
    }
  }

  public function match_routes() {
    foreach ($this->routes as $route) {
      if ($route->is_matched) {
        $this->set_route($route);
        break;
      }
    }

    # If no route was found, check $_REQUEST params
    if (!$this->route_found) {
      if (isset($_REQUEST['controller'])) {
        $this->controller = $_REQUEST['controller'];
        $this->route_found = true;
      }

      $this->action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'index';

      if (isset($_REQUEST['module'])) {
        $this->module = $_REQUEST['module'];

        $this->module_path = str_replace('__', '/', $this->module).'/';
      }
    }
  }
}

class Route {
  public $is_matched = false;
  public $params;
  public $url;
  private $conditions;

  function __construct($url, $request_uri, $target, $conditions) {
    $this->url = $url;
    $this->params = array();
    $this->conditions = $conditions;
    $p_names = array();
    $p_values = array();

    // Extract pattern names (catches :controller, :action, :id, etc)
    preg_match_all('@:([\w]+)@', $url, $p_names, PREG_PATTERN_ORDER);
    $p_names = $p_names[0];

    // Make a version of the request with and without the '?x=y&z=a&...' part
    $pos = strpos($request_uri, '?');
    if ($pos) {
      $request_uri_without = substr($request_uri, 0, $pos);
    } else {
      $request_uri_without = $request_uri;
    }

    // Determine REST verb
    $request_method = 'get';
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
      $request_method = isset($_POST['_method']) && in_array(strtolower($_POST['_method']), array('put', 'delete')) ? strtolower($_POST['_method']) : 'post';
    }
    $this->verb = isset($conditions['via']) ? strtolower($conditions['via']) : 'get';

    // Look over requests
    foreach (array($request_uri, $request_uri_without) as $request) {
      # Check for placeholders
      $url_regex = preg_replace_callback('@:[\w]+@', array($this, 'regex_url'), $url);
      $url_regex .= '/?';

      // Check if matched
      if (preg_match('@^'.$url_regex.'$@', $request, $p_values) && $this->verb == $request_method) {
        // At this point, we have a matched route. Go ahead and set the params in this object
        // and add them to the PHP $_REQUEST.

        array_shift($p_values);
        foreach ($p_names as $index => $value) {
          $this->params[substr($value, 1)] = urldecode($p_values[$index]);
        }
        foreach ($target as $key => $value) {
          $this->params[$key] = $value;
        }

        $this->is_matched = true;

        // Merge matched route params into $_REQUEST object
        $_REQUEST = array_merge($_REQUEST, $this->params);
        break;
      }
    }

    unset($p_names);
    unset($p_values);
  }

  function regex_url($matches) {
    $key = str_replace(':', '', $matches[0]);
    if (array_key_exists($key, $this->conditions)) {
      return '('.$this->conditions[$key].')';
    } else {
      return '([a-zA-Z0-9_&\+\-%]+)';
    }
  }
}

//require('router.php');
//
//$r = new Router();
//
//// The default page people will see, e.g. this is a
//// mapping for http://<location>/example
//// -> runs HelloController->overview()
//$r->map('', "Hello::overview");
//
//// mapping for http://<location>/example/hello/en
//// -> runs HelloController->world()
//$r->map("hello", "Hello::world");
//$r->map("hello/en", "Hello::world");
//
//// mapping for http://<location>/example/hello/fr
//// -> runs HelloController->monde()
//$r->map("hello/fr", "Hello::monde");
//
//// mapping for http://<location>/example/<filename>.<txt|json>
//// -> runs FileController->download($filename, $ext)
////    where $filename matches <filename> and $ext is either 'txt' or 'json'
//$r->map(":filename\.:ext",
//        "File::download",
//        // regular expressions determine what is valid for 'filename' and 'ext'
//        array("filename"=>'[\w\d_-]+', "ext"=>"(txt|json)"));
//
//// generic mapping for http://<location>/example/<controller>/<action>
//// -> for example http://<location>/example/person/all will run
////    PersonController->all()
//// -> or http://<location>/example/organisation/add will run
////    OrganisationController->add()
//$r->map(":controller/:action");
//
//// generic mapping for http://<location>/example/<controller>/<id>
//// -> for example http://<location>/example/person/2 will run
////    PersonController->view(2)
//// -> or http://<location>/example/organisation/3 will run
////    OrganisationController->view(3)
//$r->map(":controller/:id",
//        array('action'=>'view'),
//        array("id"=>"[0-9]+")); // only allow numeric values for 'id'
//
//$r->run();