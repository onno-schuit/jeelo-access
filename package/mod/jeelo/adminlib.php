<?php
/**
 * This file contains the classes for the admin settings of the assign module.
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** Include adminlib.php */
require_once($CFG->libdir . '/adminlib.php');

/**
 * Admin external page that displays a list of the installed submission plugins.
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class jeelo_admin_page_manage_assign_plugins extends admin_externalpage {

    /** @var string the name of plugin subtype */
    private $subtype = '';

    /**
     * The constructor - calls parent constructor
     *
     * @param string $subtype
     */
    public function __construct($subtype) {
        $this->subtype = $subtype;
        parent::__construct('manage' . $subtype . 'plugins', get_string('manage' . $subtype . 'plugins', 'assign'),
                new moodle_url('/mod/assign/adminmanageplugins.php', array('subtype'=>$subtype)));
    }
}