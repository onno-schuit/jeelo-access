<?php

function xmldb_jeelo_install() {
  global $DB;

  $DB->sql("INSERT INTO jeelo_access_defaults (`key`, `value`) VALUES('%s', '%s');", 'access', '0');
  $DB->sql("INSERT INTO jeelo_access_defaults (`key`, `value`) VALUES('%s', '%s');", 'expanded', 'quiz');
}

