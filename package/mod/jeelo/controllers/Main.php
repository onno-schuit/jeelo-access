<?php

global $CFG;
require_once($CFG->soda2_lib . 'class.Controller.php');

require_once('./../../course/lib.php');

function get_status($value) {
  return ($value) ? 'ok icon-green' : 'remove icon-red';
}

/***
 * Function get_status_column
 * Checks column  values and returns it's stauts
 * 
 * @param array $column - arary of true/false values
 * @param boolean $id_only
 */
function get_status_column($column, $id_only=false) {
    $status = 1; //'ok icon-green';

    // Check if there is at least one 'false' in $column
    if (in_array(false, $column)) {
        $status = 2; //'adjust icon-yellow';

        // Check if there is at least one 'true' in $column
        if (!in_array(true, $column)) {
            $status = 0; //'remove icon-red';
        }
    }

    if ($id_only) {
      return $status;
    }
    if ($status == 1) {
      $str = 'ok icon-green';
    } elseif ($status == 2) {
      $str = 'adjust icon-yellow';
    } else {
      $str = 'remove icon-red';
    }

    return $str;
}

function visibility($key, $expanded) {
  $visible = true;
  if (!in_array($key, $expanded)) {
    $visible = false;
  }

  if (!$visible) {
    return ' style="display:none"';
  } else {
    return '';
  }
}

function mod_name_plural($mods, $key, $value) {
  return $mods[$key][$value];
}

function ak($arry, $key, $key2) {
  $key = $key . '_' . $key2;

  if (array_key_exists($key, $arry)) {
      return $arry[$key];
  }
  $key2;
}

class Main extends Soda2_Controller {
  public function __construct($soda2, $view = 'index') {
    parent::__construct($soda2, $view);
    $this->module = 'jeelo';


  }

  public function index() {
    $courses = $this->db->sql("SELECT * FROM {course} WHERE id != 1 AND format = 'jeelo' ORDER BY id ASC");

    $can_view = false;
    foreach($courses as $course) {
      $context = get_context_instance(CONTEXT_COURSE, $course['id']);
      if (has_capability('moodle/course:update', $context)) {
        $can_view = true;
      }
    }

    if (!can_view) {
      // Check if user can change at least one course
      @include_once('lib/weblib.php');
      print_error('nopermissions', '', '', 'moodle/course:update');
      die();
    }

    $this->set('heading', 'Courses');

    $this->set('courses', $courses);

    $this->set('settings_heading', 'Settings');
    $this->set('root_url', $this->base_url);

  }

  public function course_view($id) {
    $instance = $this->db->sql(sprintf("SELECT course FROM {course_modules} WHERE id = '%s'", $id), true);
    if (!is_null($instance)) {
      $this->raw = true;
      $out = '<script type="text/javascript">window.location.href="' .$this->base_url . '/mod/jeelo/?/course/' . $instance['course'] . '/";</script>';

      return $out;
    }
    die();
  }

  public function course($id) {
    $this->course_id = $id;

    $course = $this->db->record('course', array('id'=>$id));
    $this->navbar_add($course['fullname'], '/course/view.php?id=' . $id);

    $this->_get_context();

    $context = get_context_instance(CONTEXT_COURSE, $id);
    if (!has_capability('moodle/course:update', $context)) {
      @include_once('lib/weblib.php');
      print_error('nopermissions', '', '', 'moodle/course:update');
      die();
    }

    $mod_data = $this->_get_mods($id);
    $my_mods = $mod_data[0];
    $plural_mods = $mod_data[1];

    $this->set('remods', $mod_data[2]);

    $default = $this->_mod_settings('access', 0); // defaults to false



    $users = $this->_get_users($id);

    $course = $this->db->record('course', array('id'=>$id));
    $this->set('course_id', $course['id']);

    $this->set('expanded', array());
    $table = array();
    if (count($users) > 0) {
      foreach($users as $user) {
	$_user = array('user'=>$user, 'mods'=>array());
	
	foreach($my_mods as $modname=>$mod) {
	  if (!array_key_exists($modname, $_user['mods'])) {
	    $_user['mods'][$modname] = array();
	  }

	  $access = NULL;
	  if (count($mod['instances']) > 0) {
	    $access = $this->db->sql(sprintf("SELECT activity, level FROM {jeelo_access}
                                        WHERE type = '%s'
                                            AND activity IN (%s)
                                            AND userid = '%s'",
					     $modname,
					     implode(',', $mod['instances']),
					     $user['id']));
	  }

	  foreach($mod['instances'] as $instance) {
	    if (!is_null($access) && array_key_exists($instance, $access)) {
	      $_user['mods'][$modname][$instance] = $access[$instance]['level'];
	    } else {
	      // Use defaults
	      $_user['mods'][$modname][$instance] = $default;
	    }
	  }
	  
	}
	
	$table[] = $_user;
      }
      $this->set('expanded', array($modname));
    }

    $this->set('table', $table);

    $this->set('mods', $my_mods);
    $this->set('plural_mods', $plural_mods);
    $this->set('root_url', $this->base_url);
  }

  public function save_one($id) {
    // Enable raw output
    $this->json = true;

    $course = $this->db->record('course', array('id'=>$id));
    $this->set('course_id', $course['id']);

    if ($this->request->post('type', false)) {
      $saved = $this->_save_access($this->request->post('type', 'quiz'),
				   $this->request->post('id'),
				   $this->request->post('userid'),
				   $this->request->post('status'));

      if ($saved) {
	return array('status'=>'ok');
      }

    }

    return array('status' => 'error',
		 'msg' => 'Incorrect request');
  }

  public function save_user($id) {
    $this->json = true;

    $course = $this->db->record('course', array('id'=>$id));
    if ($this->request->post('userid', false)) {
      $user = $this->db->record('user', array('id'=>$this->request->post('userid')));
      $status = $this->request->post('status', 1);
      
      $mods = $this->_get_mods($id);

      $data = $mods[0];
      foreach($mods[0] as $key=>$mod) {
	foreach($mod['instances'] as $instance) {
	  $this->_save_access($key,
			      $instance,
			      $this->request->post('userid'),
			      $status);

	}
      }

      return array('status'=>'ok');
    }

    return array('status' => 'error',
		 'msg' => 'Incorrect request');
  }

  public function save_activity($id) {
    $this->json = true;
    $course = $this->db->record('course', array('id'=>$id));
    if ($this->request->post('activity', false) && $this->request->post('type', false)) {
      $status = $this->request->post('status', 1);
      $users = $this->db->sql("SELECT id FROM {user}");
      foreach ($users as $user) {
        $this->_save_access($this->request->post('type', 'quiz'),
                            $this->request->post('activity', 0),
                            $user['id'],
                            $status);
      }
      return array('status' => 'ok');
    }
    return array('status' => 'error',
		 'msg' => 'Incorrect request');
  }

  public function save_group($id) {
    $this->json = true;
    if ($this->request->post('type', false)) {
      $users = $this->db->sql("SELECT id FROM {user}");
      $status = $this->request->post('status', 1);

      $mods = $this->_get_mods($id);

      $data = $mods[0];
      if (array_key_exists($this->request->post('type', 'quiz'), $data)) {
	$items = $data[$this->request->post('type', 'quiz')]['instances'];

	foreach ($users as $user) {
	  foreach ($items as $num=>$item) {
	    $this->_save_access($this->request->post('type', 'quiz'),
				$item,
				$user['id'],
				$status);
	  }
	}

	return array('status' => 'ok');
      }
    }

    return array('status' => 'error',
                 'msg' => 'Incorrect request');
  }

  public function save_user_group($id) {
    $this->json = true;

    if ($this->request->post('userid', false) && $this->request->post('type', false) &&
        $this->request->post('status', false) !== false) {
      $mods = $this->_get_mods($id);

      $data = $mods[0];
      if (array_key_exists($this->request->post('type', 'quiz'), $data)) {
	$items = $data[$this->request->post('type', 'quiz')]['instances'];

	$user = $this->db->record('user', array('id'=>$this->request->post('userid')));

	foreach ($items as $num=>$item) {
	  $this->_save_access($this->request->post('type', 'quiz'),
			      $item,
			      $user['id'],
			      $this->request->post('status', 1));
	}
	return array('status' => 'ok');
      }
    }

    return array('status' => 'error',
                 'msg' => 'Incorrect request');
  }

  private function _save_access($type, $activity_id, $userid, $status) {
    $id = $this->db->sql(sprintf("SELECT id
                                  FROM {jeelo_access}
                                  WHERE type = '%s'
                                    AND activity = %s
                                    AND userid = '%s'",
                                 $type,
				 $activity_id,
				 $userid),
                         true);

    if (is_null($id)) {
      // Create new
      $sql = sprintf("INSERT INTO {jeelo_access} (type, activity, userid, level)
                      VALUES ('%s', '%s', '%s', '%s')",
                     $type,
		     $activity_id,
		     $userid,
		     $status);
      $this->db->insert('jeelo_access', array('type'=>$type,
					      'activity'=>$activity_id,
					      'userid'=>$userid,
					      'level'=>$status));
    } else {
      $sql = sprintf("UPDATE {jeelo_access} SET level = '%s' WHERE id = %s",
                     $status,
                     $id['id']);
      $this->db->update('jeelo_access', array('id'=>$id['id'], 'level'=>$status));
    }

    //_dump($sql);
    // Save changes
    //$i = $this->db->update($sql);

    return true;
  }

  private function _get_mods($id) {
    get_all_mods($id, &$mods, &$modnames, &$modnamesplural, &$modnamesused);

    $_settings = $this->db->sql("SELECT * FROM {jeelo_access_defaults}");
    $defaults = array();
    if (!is_null($_settings)) {
      foreach($_settings as $config) {
	$defaults[$config['config']] = $config['data'];
      }
    }

    $my_mods = array();
    $plural_mods = array();

    $pm = array();

    foreach ($mods as $mod) {
      if ($mod->modname == 'jeelo') {
	continue;
      }

      if (!array_key_exists($mod->modname, $my_mods)) {
	$my_mods[$mod->modname] = array('instances'=>array(), 'plural'=>$mod->modplural);
	if (array_key_exists($mod->modname, $defaults)) {
	  $my_mods[$mod->modname]['plural'] = $defaults[$mod->modname];
	}


	$plural_mods[$mod->modname] = array();
      }

      $my_mods[$mod->modname]['instances'][] = $mod->id;
      $plural_mods[$mod->modname][$mod->id] = $mod->name;

      $pm[$mod->id] = $mod->name;

    }
    
    $_jeelo_mod = $this->db->sql(sprintf("SELECT cm.id FROM {course_modules} cm, {modules} m 
WHERE cm.course = '%s' AND cm.module = m.id AND m.name = 'jeelo'", $id));

    if (is_null($_jeelo_mod)) {
      $_jeelo_mod = array();
    }

    $sections = get_all_sections($id);

    $sects = array();
    $psects = array();
    $remod_ids = array();

    foreach ($sections as $section) {
      if ($section->sequence !== NULL) {
	$_instances = explode(',', $section->sequence);

	if (is_null($section->name) || empty($section->name) || empty($section->sequence)) {
	  // Skip section without title or instances
	  continue;
	}
	$sect = get_course_section($section->id, $id); 

	$name = ((!is_null($section->name)) ? $section->name : ('#' . $sect->section));

	$instances = array();
	$psects[$section->id] = array();

	foreach($_instances as $instance) {
	  if ($instance !== '' && array_key_exists($instance, $pm)) {
	    $instances[] = $instance;

	    $_plural = $pm[$instance];
	    $_ = explode(' ', $_plural);

	    if (count($_) > 1 && (int)$_[0] !== 0) {
                $_plural = $_[0];
            }
	    
	    $psects[$section->id][$instance] = $pm[$instance];
	    $remod_ids[$section->id . '_' . $instance] = $_plural;
	  }
	}

	$sects[$section->id] = array('plural'=>$name,
				  'instances'=>$instances);

      }
    }
    return array($sects, $psects, $remod_ids);
    _dump($psects);

    _dump($sects);

    return array($my_mods, $plural_mods);
  }

  private function _get_context() {
    // Collect context params
    $module = $this->db->sql(sprintf("SELECT cm.id
    FROM {modules} m, {course_modules} cm
    WHERE cm.module = m.id AND cm.course = %s AND m.name = 'jeelo'", $this->course_id), true);

    $this->_context = get_context_instance(CONTEXT_MODULE, $module['id']);
  }

  private function _get_users($id) {
    $contextid = get_context_instance(CONTEXT_COURSE, $id);

    $ssql = "SELECT u.id, u.username, u.lastname, u.firstname
FROM {user} u, {role_assignments} ra, {role} r
WHERE u.id=ra.userid AND ra.roleid = r.id AND r.shortname = 'student' AND ra.contextid = {$contextid->id}";
    $users = $this->db->sql($ssql);
    return $users;
  }

  private function _mod_settings($key, $default=false) {
    $module = $this->db->sql(sprintf("SELECT j.access, j.expanded
    FROM {jeelo} j, {modules} m, {course_modules} cm
    WHERE j.id = cm.instance AND cm.module = m.id AND cm.course = %s AND m.name = 'jeelo'", $this->course_id), true);

    $param = null;
    if (!is_null($module)) {
      if (array_key_exists($key, $module)) {
      $param = $module[$key];
      }
    }

    if (is_null($param)) {
      return $default;
    } else {
      return $param;
    }
  }
}