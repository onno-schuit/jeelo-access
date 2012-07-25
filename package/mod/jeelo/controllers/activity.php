<?php

include_once("{$CFG->dirroot}/local/soda/class.controller.php");

class Activity_Controller extends controller {
    function __construct($mod_name, $mod_instance_id) {
        parent::__construct($mod_name, $mod_instance_id);
        $this->require_login();
    } // function __construct

} // class Activity_Controller 