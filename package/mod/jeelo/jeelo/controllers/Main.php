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

class Main extends Soda2_Controller {
  public function __construct($soda2, $view = 'index') {
    parent::__construct($soda2, $view);
    $this->module = 'jeelo';
  }

  public function index() {
    $courses = $this->db->sql("SELECT * FROM {course} WHERE id != 1 ORDER BY id ASC");

    $this->set('heading', 'Courses');

    $this->set('courses', $courses);

    $this->set('settings_heading', 'Settings');
  }

  public function course_view($id) {
    $instance = $this->db->sql(sprintf("SELECT course FROM {course_modules} WHERE id = '%s'", $id), true);
    if (!is_null($instance)) {
      $this->raw = true;
      $out = '<script type="text/javascript">window.location.href="' .$this->base_url . '/mod/jeelo/?/course/' . $instance['course'] . '/";</script>';

      return $out;
    }
    die($id);
  }

  public function course($id) {
    $this->course_id = $id;

    $this->_get_context();

    $mod_data = $this->_get_mods($id);
    $my_mods = $mod_data[0];
    $plural_mods = $mod_data[1];

    $default = $this->_mod_settings('access', 0); // defaults to false

    $this->set('expanded', explode(',', $this->_mod_settings('expanded', 'quiz')));

    $users = $this->db->sql("SELECT id, lastname, firstname FROM {user} ORDER BY lastname ASC");

    $course = $this->db->record('course', array('id'=>$id));
    $this->set('course_id', $course['id']);


    $table = array();
    foreach($users as $user) {
      $_user = array('user'=>$user, 'mods'=>array());

      foreach($my_mods as $modname=>$mod) {
	if (!array_key_exists($modname, $_user['mods'])) {
	  $_user['mods'][$modname] = array();
	}

	$access = $this->db->sql(sprintf("SELECT activity, level FROM {jeelo_access}
                                        WHERE type = '%s'
                                            AND activity IN (%s)
                                            AND userid = '%s'",
					 $modname,
					 implode(',', $mod['instances']),
					 $user['id']));

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

    $this->set('table', $table);

    $this->set('mods', $my_mods);
    $this->set('plural_mods', $plural_mods);
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
      
      $mods = $this->_get_mods($id);

      $data = $mods[0];
      foreach($mods[0] as $key=>$mod) {
	foreach($mod['instances'] as $instance) {
	  $this->_save_access($key,
			      $instance,
			      $this->request->post('userid'),
			      1);

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
      $users = $this->db->sql("SELECT id FROM {user}");
      foreach ($users as $user) {
        $this->_save_access($this->request->post('type', 'quiz'),
                            $this->request->post('activity', 0),
                            $user['id'],
                            1);
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

      $mods = $this->_get_mods($id);

      $data = $mods[0];
      if (array_key_exists($this->request->post('type', 'quiz'), $data)) {
	$items = $data[$this->request->post('type', 'quiz')]['instances'];

	foreach ($users as $user) {
	  foreach ($items as $num=>$item) {
	    $this->_save_access($this->request->post('type', 'quiz'),
				$item,
				$user['id'],
				1);
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
    } else {
      $sql = sprintf("UPDATE {jeelo_access} SET level = '%s' WHERE id = %s",
                     $status,
                     $id['id']);
    }
	  
    // Save changes
    $this->db->sql($sql);
    return true;
  }

  private function _get_mods($id) {
    get_all_mods($id, &$mods, &$modnames, &$modnamesplural, &$modnamesused);

    $my_mods = array();
    $plural_mods = array();

    foreach ($mods as $mod) {
      if ($mod->modname == 'jeelo') {
	continue;
      }
      if (!array_key_exists($mod->modname, $my_mods)) {
	$my_mods[$mod->modname] = array('instances'=>array(), 'plural'=>$mod->modplural);
	$plural_mods[$mod->modname] = array();
      }

      $my_mods[$mod->modname]['instances'][] = $mod->id;
      $plural_mods[$mod->modname][$mod->id] = $mod->name;
    }

    return array($my_mods, $plural_mods);
  }

  private function _get_context() {
    // Collect context params
    $module = $this->db->sql(sprintf("SELECT cm.id
    FROM {modules} m, {course_modules} cm
    WHERE cm.module = m.id AND cm.course = %s AND m.name = 'jeelo'", $this->course_id), true);

    $this->_context = get_context_instance(CONTEXT_MODULE, $module['id']);
  }

  private function _mod_settings($key, $default=false) {
    $module = $this->db->sql(sprintf("SELECT j.access, j.expanded
    FROM {jeelo} j, {modules} m, {course_modules} cm
    WHERE j.id = cm.instance AND cm.module = m.id AND cm.course = %s AND m.name = 'jeelo'", $this->course_id), true);

    $param = null;
    if (!is_null($module) && array_key_exists($key, $module)) {
      $param = $module[$key];
    }

    if (is_null($param)) {
        $param = $this->db->sql(sprintf("SELECT * FROM {jeelo_access_defaults} WHERE key='%s'", $key), true);
    }

    if (is_null($param)) {
      return $default;
    } else {
      return $param;
    }
  }
}