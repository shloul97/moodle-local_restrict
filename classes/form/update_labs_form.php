<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>
/**
 *
 * @package   local_restrict
 * @copyright 2025 Moayad Shloul <shloul97@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');



class update_labs extends moodleform {


    protected function definition() {

        global $OUTPUT, $PAGE, $CFG, $DB;

        $mform = $this->_form;

        $records = $DB->get_records('local_restrict_labs');
        $options = array('' => 'Select a lab'); // Blank value with a prompt.

        foreach ($records as $choice) {
            $options[$choice->id] = $choice->lab_name;
        }

        $mform->addElement('select', 'labid', get_string('labname', 'local_restrict'), $options);
        $mform->setType('labid', paramtype: PARAM_TEXT);

        $mform->addElement('text', 'deviceip', get_string('device_ip', 'local_restrict'));
        $mform->setType('deviceip', PARAM_TEXT);

        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('filter', 'local_restrict'));

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

    }


    function validation($data, $files) {
        return array();
    }

}
