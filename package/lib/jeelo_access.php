<?php

function jeelo_check_permissions($userid, $modname, $modid) {
  global $DB;

  $data = $DB->get_records_sql(sprintf("SELECT * FROM {jeelo_access} WHERE userid = '%s' AND type = '%s' AND activity = '%s'",
			    $userid,
			    $modname,
			    $modid));
  var_dump($data);
  if (!count($data)) {
    return false; // Default to false
  }

  foreach($data as $rights) {
    return ($rights->level == 1) ? true : false;
  }
}