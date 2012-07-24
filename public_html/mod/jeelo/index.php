<?php

require_once('../../config.php');
$CFG->soda2_lib = '../../local/soda2/';

ob_end_flush();
require_once($CFG->soda2_lib . 'class.soda2.php');

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$config = array(

  'routes' => array(
    '' => array('Main', 'index'),
    '/course\/(?<id>\d+)\//' => array('Main', 'course'),
    '/course\/(?<id>\d+)\/save_one.json/' => array('Main', 'save_one'),
    '/course\/(?<id>\d+)\/save_user.json/' => array('Main', 'save_user'),
    '/course\/(?<id>\d+)\/save_activity.json/' => array('Main', 'save_activity'),
    '/course\/(?<id>\d+)\/save_group.json/' => array('Main', 'save_group'),
    '/course\/(?<id>\d+)\/save_user_group.json/' => array('Main', 'save_user_group'),

    // Settings routes
    '/settings\//' => array('Settings', 'index')
),

  'urlstrip' => '/mod/jeelo/?',
  'base_dir' => dirname(__FILE__),
  'tpl_dir' => sprintf('%s/templates/', dirname(__FILE__)),
  'tpl_cache' => sprintf('%s/tplcache/', $CFG->dataroot),
);

$App = new Soda2($config);

$App->render();
