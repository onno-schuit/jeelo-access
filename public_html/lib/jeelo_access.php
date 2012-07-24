<?php

function jeelo_check_permissions($userid, $modname, $modid) {
  global $DB;

  $data = $DB->get_records_sql(sprintf("SELECT * FROM {jeelo_access} WHERE userid = '%s' AND type = '%s' AND activity = '%s'",
			    $userid,
			    $modname,
			    $modid));
  if (!count($data)) {
    return false; // Default to false
  }

  foreach($data as $rights) {
    return ($rights->level) ? true : false;
  }
}