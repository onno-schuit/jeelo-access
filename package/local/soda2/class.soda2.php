<?php

function soda2_error_handler($errno, $errstr, $errfile, $errline) {
  echo '<h2>Error</h2><pre>';
  var_dump($errno);
  echo '</pre><br /><pre>';
  var_dump($errstr);
  echo '</pre><br /><pre>';
  var_dump($errfile);
  echo '</pre><br /><pre>';
  var_dump($errline);
  echo '</pre>';
}

//$old_error_handler = set_error_handler("soda2_error_handler", E_ALL);

function soda2_exception_handler(Exception $e)
{    
    $errors = array(
        E_USER_ERROR        => "User Error",
        E_USER_WARNING        => "User Warning",
        E_USER_NOTICE        => "User Notice",
        );
        
    echo $errors[$e->getCode()].': '.$e->getMessage().' in '.$e->getFile().
        ' on line '.$e->getLine()."\n";
    echo $e->getTraceAsString();
}

//set_exception_handler('soda2_exception_handler');

/**
 * Soda2_Config class
 * Configuration handler
 *
 */
class Soda2_Config {

  /**
   * Soda2_Config constructor
   *
   * @param $config array
   */
  public function __construct($config) {
    $this->config = $config;
  }

  /**
   * Config getter
   *
   * @param $key string
   * @param $default_value, false by default
   *
   * @return $value $this->config[$key] or $default_value
   */
  public function get($key, $default_value = false) {
    if (array_key_exists($key, $this->config)) {
      $value = $this->config[$key];
    } else {
      $value = $default_value;
    }

    return $value;
  }
}

class Soda2 {
  /**
   * Soda2 constructor
   *
   * @param $config configuration array
   */
  function __construct($config) {

    // Init Soda config
    $this->_config = new Soda2_Config($config);
    $this->_config_cache = array();

    // Url strip, used to strip extra parameters from url
    $this->urlstrip = $this->config('urlstrip', '');  // Empty by default

    // Define main url
    $_url = substr($this->config('url', $_SERVER['REQUEST_URI']),
		   strlen($this->urlstrip));
    $this->url = $_url ? $_url : '';
    // TODO:::
    $_temp = explode('?', $_SERVER['REQUEST_URI']);

    $this->url = '';
    if (count($_temp) > 1) {
      $this->url = $_temp[1];
    }

    // Main controllers directory setting
    $this->controllers_dir = $this->config('controllers', 'controllers');

    // Main routes
    $this->routes = $this->config('routes', array());
  }

  /**
   * Adds new route
   *
   * @param $url string/regexp url ^$ used as main
   * @param $controller controller class
   * @param $action action within specified controller
   */
  function addRoute($url, $controller, $action) {
    $this->routes[$url] = array($controller, $action);
  }

  function render() {
    try {
      // Find route
      $routes = $this->_resolve_route();

      $route = $routes[0];
      $params = $routes[1];

      $_controller_file = sprintf('%s/%s/%s.php',
			     $this->config('base_dir', dirname(__FILE__)),
			     $this->controllers_dir,
			     $route[0]);

      if (file_exists($_controller_file)) {
	// File exists, include it.
	@include_once($_controller_file);

	// Initializate controller
	$this->controller = new $route[0]($this, $route[1]);

	// Call view
	$result = call_user_func_array(array($this->controller, $route[1]), $params);
	
	// Render view
	$this->controller->render($result);
      } else {
	// No controller file found, damn
	$this->_dump(sprintf('No controller, damn %s', $_controller_file));
      }
    } catch (Exception $e) {
      die($e);
      $this->_dump($e->getMessage());
    }
  }

  private function _dump($data) {
    echo '<pre>';var_dump($data);echo '</pre>';
  }

  /**
   * Proxy config function
   * Memorized config param getter
   *
   * @param $key string
   * @param $default_value, false by default
   */
  function config($key, $default_value = false) {
    if (!array_key_exists($key, $this->_config_cache)) {
      $this->_config_cache[$key] = $this->_config->get($key, $default_value);
    }
    return $this->_config_cache[$key];
  }


  /**
   * Resolve route
   *
   */
  private function _resolve_route() {
    $route = array('__soda2_default', 'noroute');
    $params = array();

    // Do simple one-to-one check
    if (array_key_exists($this->url, $this->routes)) {
      $route = $this->routes[$this->url];
    } else {
      foreach ($this->routes as $key=>$rt) {
        if (empty($key)) {
          continue;
        }
        preg_match_all($key,
                   $this->url,
                   $matches,
                   PREG_SET_ORDER);
        if (count($matches)) {
          foreach($matches[0] as $match=>$value) {
            if (is_int($match)) {
              continue;
            }
	if (empty($key)) {
	  continue;
	}
	preg_match_all($key,
		       $this->url,
		       $matches,
		       PREG_SET_ORDER);
	if (count($matches)) {
	  foreach($matches[0] as $match=>$value) {
	    if (is_int($match)) {
	      continue;
	    }

	    $params[$match] = $value;
	  }
	  $route = $this->routes[$key];
	}
            $params[$match] = $value;
          }
          $route = $this->routes[$key];
        }
      }
      // Some black magic here
    }
    return array($route, $params);
  }

}
