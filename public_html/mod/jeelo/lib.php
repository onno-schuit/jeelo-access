<?php

  // Include config
@include_once('../../config.php');


//include the RainTPL class
include "lib/rain.tpl.class.php";

raintpl::configure("base_url", null );
raintpl::configure("tpl_dir", "templates/" );
raintpl::configure("cache_dir", $CFG->dataroot . "/tplcache/" );

//initialize a Rain TPL object
$tpl = new RainTPL;
