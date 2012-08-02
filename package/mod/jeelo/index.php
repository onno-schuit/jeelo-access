<?php

require_once('../../config.php');
require_once('./lib.php');

require_login();

$App = new Soda2($config);

$App->render();
