<?php

require_once('../../config.php');
require_once('./lib.php');

$config['urlstrip'] = '/mod/jeelo/view.php?';

$App = new Soda2($config);

$App->render();
