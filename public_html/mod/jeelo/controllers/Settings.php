<?php

global $CFG;
require_once($CFG->soda2_lib . 'class.Controller.php');

class Settings extends Soda2_Controller {
  public function __construct($soda2, $view = 'index') {
    parent::__construct($soda2, $view);
    $this->module = 'jeelo';
  }

  public function index() {

    $settings = $this->db->sql("SELECT * FROM {jeelo_access_defaults}");
    _dump($settings);
    $this->set('settings', $settings);

    if ($this->request->method == 'POST') {
      foreach ($settings as $id=>$config) {
	if ($this->request->post($config['key'], null) == null) {
	  $this->db->sql(sprintf("UPDATE {jeelo_access_defaults} SET value = '%s' WHERE key = '%s'",
				 $config['key'],
				 $this->request->post($config['key'])));
	}
      }
      $this->set('updated', true);
    }
  }
}
