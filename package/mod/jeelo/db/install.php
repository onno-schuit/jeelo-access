<?php

function xmldb_jeelo_install() {
  global $DB;

  $DB->get_records_sql(sprintf("INSERT INTO jeelo_access_defaults (`key`, `value`) VALUES('%s', '%s');", 'access', '0'));
  $DB->get_records_sql(sprintf("INSERT INTO jeelo_access_defaults (`key`, `value`) VALUES('%s', '%s');", 'expanded', 'quiz'));
}

