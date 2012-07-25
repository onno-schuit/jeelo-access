<?php
/**
 * Allows the admin to manage jeelo access plugins
 *
 * @package    mod_jeelo
 * @copyright 2012 Solin {@link http://www.solin.nl}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** Include config.php */
require_once(dirname(__FILE__) . '/../../config.php');
/** Include adminlib.php */
require_once($CFG->dirroot.'/mod/jeelo/adminlib.php');

// create the class for this controller
$pluginmanager = new jeelo_plugin_manager(required_param('subtype', PARAM_PLUGIN));

$PAGE->set_context(get_system_context());

// execute the controller
$pluginmanager->execute(optional_param('action', null, PARAM_PLUGIN), optional_param('plugin', null, PARAM_PLUGIN));
