jeelo-access
============

Jeelo Individual Access

============

Routing

============

Jeelo access routing is done with help of simple _soda2_ micro framework.
Hereby jeelo module initialization. (/mod/jeelo/lib.php:397:421)

```
# soda2_lib configuration dir is needed by soda2
$CFG->soda2_lib = $CFG->dirroot . '/local/soda2/';

require_once($CFG->soda2_lib . 'class.soda2.php');

# Soda2 configuration
$config = array(
  # Routing, assigns regular expression to array({Controller}, {Action})
  # Controller - php file and Soda2_Controller instance located in 'controllers' directory within jeelo module
  #   ('controllers' directory name could be changed by setting $config->controllers variable)
  # Action - function name within Soda2_Controller instance
  #
  # Any regexp parameter will be parsed and passed into view function
  #  i.e.: for '/course\/(?<id>\d+)\//' => array('Main', 'course'),
  #        uri /course/2/ will be parsed and '2' will be passed as Main->course(2);
  'routes' => array(
    '' => array('Main', 'index'),
    '/course\/(?<id>\d+)\//' => array('Main', 'course'),
    '/id=(?<id>\d+)/' => array('Main', 'course_view'),
    '/course\/(?<id>\d+)\/save_one.json/' => array('Main', 'save_one'),
    '/course\/(?<id>\d+)\/save_user.json/' => array('Main', 'save_user'),
    '/course\/(?<id>\d+)\/save_activity.json/' => array('Main', 'save_activity'),
    '/course\/(?<id>\d+)\/save_group.json/' => array('Main', 'save_group'),
    '/course\/(?<id>\d+)\/save_user_group.json/' => array('Main', 'save_user_group'),

    // Settings routes
    '/settings\//' => array('Settings', 'index')
),

  # urlstrip, horrible way to identify what part of URI should be removed on url-resolving step.
  'urlstrip' => '/mod/jeelo/?',
  
  # base_dir, internal soda2 variable, identifies base directory name for controllers, templates and other includes
  'base_dir' => dirname(__FILE__),

  # tpl_dir, templates base directory
  'tpl_dir' => sprintf('%s/templates/', dirname(__FILE__)),

  # tpl_cache, templates cache directory
  'tpl_cache' => sprintf('%s/tplcache/', $CFG->dataroot),
);
```

============

Templating

============

'Main' controller file /mod/jeelo/controllers/Main.php contains few top level functions:
* get_status($value)
* get_status_column($column, $id_only=false) 
* visibility($key, $expanded)
* mod_name_plural($mods, $key, $value)
* ak($arry, $key, $key2)

These functions(as well as all php and moodle internal ones) are automatically available within templates powered by RainTPL (http://www.raintpl.com/) templating library.

Every instance on Soda2_Controller has method ->set($key, $value), which assigns variables into template scope.

============

Main Controller overview

============
