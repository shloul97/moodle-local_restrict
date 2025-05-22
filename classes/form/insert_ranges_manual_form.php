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
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package local_restrict
 * @author Moayad Shloul
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');



class insert_ranges_manual extends moodleform {

    protected function definition() {

        global $OUTPUT, $PAGE, $CFG, $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'manualHeader', 'Manual insertion');
        $mform->setExpanded('manualHeader', true); // false = collapsed by default


        $records = $DB->get_records('local_restrict_labs');



        $options = array('' => 'Select a lab'); // Blank value with a prompt

        foreach ($records as $choice) {
            $options[$choice->id] = $choice->lab_name;
        }


        $mform->addElement('select','labid',"Labs",$options);
        $mform->addRule('labid', get_string('required'), 'required', null, 'client');

        $mform->setType('labid', PARAM_TEXT);


        $mform->addElement('textarea', 'manualIps', 'IPs', ['placeholder' => "One IP per line... Exp:\n127.0.1.1 \n127.0.0.1\n...",'rows="10" cols="10"']);
        $mform->addRule('manualIps', get_string('required'), 'required', null, 'client');

        $buttonarray[] = $mform->createElement('submit', 'submitbutton', "Add");

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);





    }


    function validation($data, $files)
    {
        return array();
    }

}
