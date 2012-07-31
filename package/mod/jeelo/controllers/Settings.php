<?php

global $CFG;
require_once($CFG->soda2_lib . 'class.Controller.php');

class Settings extends Soda2_Controller {
  public function __construct($soda2, $view = 'index') {
    parent::__construct($soda2, $view);
    $this->module = 'jeelo';
  }

  public function index() {

    $modules = $this->db->sql("SELECT * FROM {modules}");
    
    $_settings = $this->db->sql("SELECT * FROM {jeelo_access_defaults}");
    
    $settings = array();

    if (!is_null($_settings)) {
      foreach($_settings as $config) {
	$settings[$config['config']] = $config['data'];
      }
    }

    foreach ($modules as $module) {
      if ($module['name'] !== 'jeelo') {
	if (!array_key_exists($module['name'], $settings)) {
	  $settings[$module['name']] = '';
	}
      }
    }

    $this->set('settings', $settings);

    if ($this->request->method == 'POST') {
      if (!is_null($settings)) {
	foreach ($settings as $id=>$config) {
	  if ($this->request->post($id, '') !== '') {
	    $this->db->insert('jeelo_access_defaults',
			      array('config'=>$id,
				    'data'=>$this->request->post($id)));
	  }
	}
      }

      $this->set('updated', true);
    }
  }
}
