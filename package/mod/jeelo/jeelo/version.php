<?php
  // example contents for version.php
  defined('MOODLE_INTERNAL') || die();

  $module->version   = 2012070400;       // The current module version (Date: YYYYMMDDXX)
  $module->requires  = 2011112900;       // Requires this Moodle version
  $module->component = 'mod_jeelo';       // Full name of the plugin (used for diagnostics)
  $module->cron      = 0;
  $module->visible   = true;