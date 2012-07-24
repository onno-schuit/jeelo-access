<?php

function xmldb_jeelo_install() {
  global $DB;

  $default_access = array('key' => 'access',
			  'value' => '0'); // Set default access to false

  $DB->insert_record('jeelo_access_defaults', (object)$default_access);

  $default_expanded = array('key' => 'expanded',
			    'value' => 'quiz');

  $DB->insert_record('jeelo_access_defaults', (object)$default_expanded);
}
