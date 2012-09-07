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

  /**
   * Index view
   *
   * Main view, displays list of courses with 'format' = 'jeelo'
   *
   */
  public function index() {
    # Get list of available courses with correct format
    $courses = $this->db->sql("SELECT * FROM {course} WHERE id != 1 AND format = 'jeelo' ORDER BY id ASC");

    # Check if this user is able to view courses
    $can_view = false;
    foreach($courses as $course) {
      $context = get_context_instance(CONTEXT_COURSE, $course['id']);
      if (has_capability('moodle/course:update', $context)) {
        $can_view = true;
      }
    }

    if (!$can_view) {
      // Check if user can change at least one course
      @include_once('lib/weblib.php');
      print_error('nopermissions', '', '', 'moodle/course:update');
      die();
    }

    # Assigning template variables
    # Page heared
    $this->set('heading', 'Courses');

    # Courses list
    $this->set('courses', $courses);

    # Settings button name
    $this->set('settings_heading', 'Settings');

    # Root uri, needed by soda2
    $this->set('root_url', $this->base_url);

  }

  /**
   * Course_view view
   *
   * Processes calls to /mod/jeelo/view.php
   * Forwards user to correct /course/{id}/ view
   *
   * @param int $id - course_module instance id
   */
  public function course_view($id) {
    $instance = $this->db->sql(sprintf("SELECT course FROM {course_modules} WHERE id = '%s'", $id), true);
    if (!is_null($instance)) {
      $this->raw = true;
      $out = '<script type="text/javascript">window.location.href="' .$this->base_url . '/mod/jeelo/?/course/' . $instance['course'] . '/";</script>';

      return $out;
    }
    die();
  }

  /**
   * Course view
   *
   * Main view for this controller, builds table of users/activities and checks rights
   *
   * @param int $id - course id
   * @return rendered templates/Main/course.html
   */
  public function course($id) {
    # Assign course_id variable for internal use
    $this->course_id = $id;

    # Fetch course data
    $course = $this->db->record('course', array('id'=>$id));
    
    # Update navbar
    $this->navbar_add($course['fullname'], '/course/view.php?id=' . $id);

    # Assing course_name template variable
    $this->set('course_name', $course['fullname']);

    # Soda2 internal function, collects context for moodle use
    $this->_get_context();

    # Get CONTEXT_COURSE and check if this user is able to update('moodle/course:update') course
    $context = get_context_instance(CONTEXT_COURSE, $id);
    if (!has_capability('moodle/course:update', $context)) {
      @include_once('lib/weblib.php');
      print_error('nopermissions', '', '', 'moodle/course:update');
      die();
    }

    # Get all course modules
    $mod_data = $this->_get_mods($id);
    # Actual modules array
    $my_mods = $mod_data[0];
    # Plural module names
    $plural_mods = $mod_data[1];

    # Assigning module's fullnames array into template variable
    $this->set('remods', $mod_data[2]);

    # Get access_default value
    $access_default = $this->_mod_settings('access', 0); // defaults to false

    # Get Users list
    $users = $this->_get_users($id);
  
    # Set tpl variable
    $this->set('course_id', $course['id']);

    # Set initial array of expanded topics
    $this->set('expanded', array());

    # Init access table
    $table = array();
    if (count($users) > 0) {
      # Loop over users and get access rights for each one
      foreach($users as $user) {
	      $_user = array('user'=>$user, 'mods'=>array());
	
        # Loop over course modules and assign access rights for particular user
	      foreach($my_mods as $modname=>$mod) {
	        if (!array_key_exists($modname, $_user['mods'])) {
	          $_user['mods'][$modname] = array();
	        }

	        $access = NULL;
          # Get access rights for all activities in this topic for this user
	        if (count($mod['instances']) > 0) {
	            $access = $this->db->sql(sprintf("SELECT activity, level FROM {jeelo_access}
                                        WHERE type = '%s'
                                            AND activity IN (%s)
                                            AND userid = '%s'",
					     $modname,
					     implode(',', $mod['instances']),
					     $user['id']));
	        }

          # Loop over topic activities
	        foreach($mod['instances'] as $instance) {
            # Check if this activity has access record in database
	          if (!is_null($access) && array_key_exists($instance, $access)) {
	            $_user['mods'][$modname][$instance] = $access[$instance]['level'];
	          } else {
	            // Use defaults
	            $_user['mods'][$modname][$instance] = $default;
	          }
	        } # eof foreach $mod['instances'] as $instance
	  
	      } # eof foreach($my_mods as $modname=>$mod)
	
        # Add user into global table
	      $table[] = $_user;
      }

      # Simple hack, last topic is expanded
      # - On second thoughts, client doesn't want any column expanded by default
      //$this->set('expanded', array($modname));
    }

    $this->set('table', $table);

    # Some template variables
    $this->set('mods', $my_mods);
    $this->set('plural_mods', $plural_mods);
    $this->set('root_url', $this->base_url);
  } // function course

  /**
   * save_one view
   *
   * Save one activity for one user
   *
   * @param int $id - course ID
   * @post_param strnig 'type' - activity type (topic)
   * @post_param string 'id'   - activity ID within course
   * @post_param int 'userid'  - user ID
   * @post_param int 'status'  - new activity status
   * @return json string with status
   */
  public function save_one($id) {
    // Enable JSON output
    $this->json = true;

    # Get course data
    $course = $this->db->record('course', array('id'=>$id));
    $this->set('course_id', $course['id']);

    if ($this->request->post('type', false)) {
      # Save accesss
      $saved = $this->_save_access($this->request->post('type', 'quiz'),
				   $this->request->post('id'),
				   $this->request->post('userid'),
				   $this->request->post('status'));

      # Saved, return response as json string: {'status': 'ok'}
      if ($saved) {
	  return array('status'=>'ok');
      }

    }

    # Something gone wrong, return status error
    return array('status' => 'error',
		 'msg' => 'Incorrect request');
  }

  /**
   * Save user view
   *
   * Saves whole line of activities in all course topics for specified user
   *
   * @param int $id - course ID
   * @post_param int 'userid' - user ID
   * @post_param int 'status' - new activities status
   * @return json string with status
   */
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

  /**
   * Save activity view
   *
   * Saves activity status for all users (whole column)
   * 
   * @param int $id - course ID
   * @post_param int 'activity' - activity ID
   * @post_param int 'status'   - new activity status
   * @return json string with status
   */
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

  /**
   * Save group view
   *
   * Saves all activities for all users within one topic (i.e. un/expanded set of collumns)
   *
   * @param int $id - course ID
   * @post_param string 'type'  - group type (topic type)
   * @post_param int 'status'   - new activity status
   * @return json string with status
   */
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

  /**
   * Save user group view
   *
   * Saves all activities for specified user within one topic
   *
   * @param int $id - course ID
   * @post_param string 'type'  - group type (topic type)
   * @post_param int 'user'     - user ID
   * @post_param int 'status'   - new activity status
   * @return json string with status
   */
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

  /**
   * Internal _save_access function
   *
   * Saves/Updates database records with access permissions
   *
   * @param string $type     - activity group type (topic)
   * @param int $activity_id - activity ID
   * @param int $userid      - user ID
   * @param int $status      - access permissions string 0/1 (disable/enable)
   * @return save status
   */
  private function _save_access($type, $activity_id, $userid, $status) {
    # Get access permissions with specofied params (if any)
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
      // Create new permissions
      $this->db->insert('jeelo_access', array('type'=>$type,
					      'activity'=>$activity_id,
					      'userid'=>$userid,
					      'level'=>$status));
    } else {
      # Update existing ones
      $this->db->update('jeelo_access', array('id'=>$id['id'], 'level'=>$status));
    }

    return true;
  }

  /**
   * internal _get_mods function
   *
   * Gets list of installed modules(topics) and all activities with permissions within course
   *
   * @param int $id - Course ID
   * @return array [0] - Topics list
   *               [0] - Plural topic names
   *               [2] - Topic activities IDs (format: topic->id . '_' . activity->id)
   */
  private function _get_mods($id) {
    # Get all mods from moodle
    get_all_mods($id, &$mods, &$modnames, &$modnamesplural, &$modnamesused);

    # Get access defaults
    $_settings = $this->db->sql("SELECT * FROM {jeelo_access_defaults}");
    $defaults = array();
    if (!is_null($_settings)) {
      foreach($_settings as $config) {
	$defaults[$config['config']] = $config['data'];
      }
    }

    # Init modules array
    $my_mods = array();

    # Init modules activities plural names array
    $plural_mods = array();

    $pm = array();

    # Iterate over all modules and activities
    foreach ($mods as $mod) {
      # Skip 'jeelo' module
      if ($mod->modname == 'jeelo') {
	continue;
      }

      # Add module into $my_mods array, if it does not exist
      if (!array_key_exists($mod->modname, $my_mods)) {
	$my_mods[$mod->modname] = array('instances'=>array(), 'plural'=>$mod->modplural);

	if (array_key_exists($mod->modname, $defaults)) {
	  # Overwrite module plural name with one set by admin
	  $my_mods[$mod->modname]['plural'] = $defaults[$mod->modname];
	}

	# Set module instance plural names array
	$plural_mods[$mod->modname] = array();
      }

      # Add module instance
      $my_mods[$mod->modname]['instances'][] = $mod->id;
      # Add module instance plural name
      $plural_mods[$mod->modname][$mod->id] = $mod->name;

      # Add module instance plural name into simplified plural names array
      $pm[$mod->id] = $mod->name;

    }
    
    # Get module 'jeelo' for specified course
    $_jeelo_mod = $this->db->sql(sprintf("SELECT cm.id FROM {course_modules} cm, {modules} m 
WHERE cm.course = '%s' AND cm.module = m.id AND m.name = 'jeelo'", $id));

    # Set defaults
    if (is_null($_jeelo_mod)) {
      $_jeelo_mod = array();
    }

    # Get all topics
    $sections = get_all_sections($id);

    # Topics list
    $sects = array();
    # Plural topic names
    $psects = array();
    # Topic IDs
    $remod_ids = array();

    # Iterate over all topics
    foreach ($sections as $section) {
      # Use only topics with at least one activity
      if ($section->sequence !== NULL) {
	$_instances = explode(',', $section->sequence);

	if (is_null($section->name) || empty($section->name) || empty($section->sequence)) {
	  // Skip section without title or instances
	  continue;
	}

	# Get section details
	$sect = get_course_section($section->id, $id); 

	# Get section name
	$name = ((!is_null($section->name)) ? $section->name : ('#' . $sect->section));

	# Initialize instances array
	$instances = array();
	# Initialize plural names array
	$psects[$section->id] = array();

	# Iterate over all section instances
	foreach($_instances as $instance) {
	  # Check if instance id is not empty and located in module instances array
	  if ($instance !== '' && array_key_exists($instance, $pm)) {
	    $instances[] = $instance;
	    
	    # Get plural name for module instance
	    $_plural = $pm[$instance];

	    # Expand first integer from titles like '22 Test name' -> 22
	    $_ = explode(' ', $_plural);
	    if (count($_) > 1 && (int)$_[0] !== 0) {
                $_plural = $_[0];
            }
	    
	    # Add plural name into plural section names array
	    $psects[$section->id][$instance] = $pm[$instance];
	    # Add topic ids
	    $remod_ids[$section->id . '_' . $instance] = $_plural;
	  }
	}

	$sects[$section->id] = array('plural'=>$name,
				  'instances'=>$instances);

      }
    }

    return array($sects, $psects, $remod_ids);
  }

  /**
   * Internal function get_context
   *
   * Gets CONTEXT_MODULE connected with this course, needed by moodle
   */
  private function _get_context() {
    // Collect context params
    $module = $this->db->sql(sprintf("SELECT cm.id
    FROM {modules} m, {course_modules} cm
    WHERE cm.module = m.id AND cm.course = %s AND m.name = 'jeelo'", $this->course_id), true);

    $this->_context = get_context_instance(CONTEXT_MODULE, $module['id']);
  }

  /**
   * Internal function get users
   *
   * Gets list of users assigned to this module with role 'student'
   *
   * @param int $id - course ID
   * @return array list of users (keys: 'id', 'username', 'lastname', 'firstname'
   */
  private function _get_users($id) {
    $contextid = get_context_instance(CONTEXT_COURSE, $id);

    $ssql = "SELECT u.id, u.username, u.lastname, u.firstname
FROM {user} u, {role_assignments} ra, {role} r
WHERE u.id=ra.userid AND ra.roleid = r.id AND r.shortname = 'student' AND ra.contextid = {$contextid->id}
ORDER BY u.lastname ASC";
    $users = $this->db->sql($ssql);
    return $users;
  }

  /**
   * Internal function mod_settings
   *
   * Get module settings for this course
   *
   * @param string $key    - settings key
   * @param mixed $default - settings defaults to be used when key is not present in database
   */
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
