<?php
class View {
  /**
   * @var
   */
  public $controller_object;
  /**
   * @var array
   */
  public $view_flow = array();

  /**
   * Initializer will store reference to controller object
   * @param $controller_object
   */
  public function __construct($controller_object) {
    $this->controller_object = $controller_object;

    $this->compiled = false;
  }

  /**
   * Renders the view template
   * @param $script
   * @return string
   */
  public function render($script) {
    if ($this->compiled) return;

    ob_start();
    $this->_include($script);
    return ob_get_clean();
  }

  /**
   * Loads the template
   * @throws Exception
   */
  protected function _include() {
    # Add variable to view template
    if($this->controller_object->variables != null) {
      foreach($this->controller_object->variables as $name => $value) {
        ${$name} = $value;
      }
    }

    include_once BASEDIR.'app/helpers/application_helpers.php';
    $helpers = new ApplicationHelper($this->controller_object->application);

    $template = func_get_arg(0);

    if(!file_exists(BASEDIR."app/views/{$template}")) {
      throw new Exception("{$template} view does not exist", 404);
    };

    $file = file_get_contents(BASEDIR."app/views/{$template}");

    // Replace php print output tags with php equivalent
    $file = preg_replace("#<\\?= (.*?) \\?>#", "<?php print \\1; ?>", $file);

    // This is necessary if PHP short tags are turned off.
    $file = preg_replace("#<\\? (.*?) \\?>#", "<?php \\1; ?>", $file);

    # TODO: Add template renderer. Convert template files
    # to intermediate code. Have some sort of check if the file changed to reparse
    # the view.
//      ob_start();
    eval("?> $file <?php ");
//      print ob_get_clean();exit;

    # TODO: Mime recognition, absolute/relative paths,
//    include BASEDIR."app/views/{$template}";
  }

  /**
   * Make all variables in controller available to view
   * @param $key
   * @return null
   */
  public function __get($key) {
    return (isset($this->$key) ? $this->$key : null);
  }

  /**
   * @param $name
   * @param $content
   */
  public function content_for($name, $content) {
    $this->view_flow[$name] = $content;
  }

  /**
   * @param $name
   */
  public function content_for_exists($name) {
    if (array_key_exists($name, $this->view_flow)) {
      return true;
    }

    return false;
  }

  /**
   * @param $name
   */
  public function generate($name) {
    if (array_key_exists($name, $this->view_flow)) {
      print $this->view_flow[$name];
      return;
    }

    return false;
  }
}