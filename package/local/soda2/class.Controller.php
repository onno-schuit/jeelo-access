<?php

include('class.DB.php');
include('class.Request.php');

function _dump($data) {
  echo '<pre>';
  var_dump($data);echo '</pre>';
}

class Soda2_Controller {
  function __construct($soda2, $view = 'index') {
    $this->soda2 = $soda2;

    $this->_init_request();

    $this->setView($view);

    $this->_init_db();

    $this->_init_tpl();

    /**
     * Private $raw boolean - show/hide headers/footers
     */
    $this->raw = false;

    /**
     * Private $json boolean - convert outpout into json flag
     */
    $this->json = false;

    $this->_context = null;
  }

  public function setView($view) {
    $class = get_class($this);

    $this->view = sprintf("%s/%s", $class, $view);
  }

  /**
   * Initialize Request
   */
  private function _init_request() {
    $this->request = new Soda2_Request();
  }


  /**
   * Initialize database
   */
  private function _init_db() {
    global $DB, $CFG;
    $this->db = new Soda2_DB($DB);
    $this->db_prefix = $CFG->prefix;
  }

  /**
   * Initialize template
   */
  private function _init_tpl() {
    global $CFG;

    // Include templating
    @include_once('library/rain.tpl.class.php');

    raintpl::configure("base_url", null);
    raintpl::configure("tpl_dir", $this->soda2->config("tpl_dir", "templates/"));
    raintpl::configure("cache_dir", $CFG->dataroot . "/tplcache/" );
    
    raintpl::configure( 'path_replace', false );

    $this->base_url =  $CFG->wwwroot;

    $this->set_global("root_url", $this->base_url);

    //initialize a Rain TPL object
    $this->tpl = new RainTPL();
  }

  /**
   * Set template global
   */
  public function set_global($name, $value) {
    raintpl::configure($name, $value);
  }

  /**
   * Set template variable
   *
   * @param $key template variable name
   * @param $value value
   */
  public function set($key, $value) {
    $this->tpl->assign($key, $value);
  }

  function navbar_add($str, $url) {
      global $PAGE;
      $PAGE->navbar->add($str, new moodle_url($url));
  }

  function render($result){
    if ($this->raw) {
      echo $result;
    } elseif ($this->json) {
      echo json_encode($result);
    } else {
      global $OUTPUT, $PAGE;

      if (!is_null($this->_context)) {
	$PAGE->set_context(null);//$this->_context);
      }

      $class = get_class($this);
      $PAGE->set_url('/mod/' . $this->module . '/index.php');
      $PAGE->set_heading('Jeelo');
      $PAGE->set_pagelayout('incourse');
      
      $strplural = get_string("modulenameplural", $this->module);

      $PAGE->navbar->add($strplural);
      $PAGE->set_title($strplural);


      echo $OUTPUT->header();
      echo $OUTPUT->heading(get_string('modulenameplural', 'jeelo'), 2);

      echo $this->tpl->draw($this->view, $return_string=true);
    
      echo $OUTPUT->footer();
    
    }
  }
}