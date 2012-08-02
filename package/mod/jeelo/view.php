<?php

require_once('../../config.php');
require_once('./lib.php');

require_login();
$config['urlstrip'] = '/mod/jeelo/view.php?';

$App = new Soda2($config);

$App->render();
