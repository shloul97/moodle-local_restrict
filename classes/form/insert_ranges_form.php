<?php
// This file is part of Moodle - http://moodle.org/
//
// Secure Exam Access plugin for Moodle
// Copyright (C) 2025 Moayad Shloul
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package   local_restrict
 * @copyright 2025 Moayad Shloul <shloul97@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');



class insert_ranges extends moodleform {

    protected function definition() {

        global $OUTPUT, $PAGE, $CFG, $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'autoHeader', get_string('auto_insertion_header', 'local_restrict'));
        $mform->setExpanded('autoHeader', false); //


        $records = $DB->get_records('local_restrict_labs');




        $options = array('' => 'Select a lab'); // Blank value with a prompt

        foreach ($records as $choice) {
            $options[$choice->id] = $choice->lab_name;
        }


        $mform->addElement('select','labid',"Labs",$options);
        $mform->setType('labid', PARAM_TEXT);
        $mform->addRule('labid', get_string('required'), 'required', null, 'client');


        $mform->addElement('text','ipstart',get_string('ipstart', 'local_restrict'));
        $mform->settype('text', PARAM_NOTAGS);
        $mform->addRule('ipstart', get_string('required'), 'required', null, 'client');

        $mform->addElement('text','ipend',get_string('ipend', 'local_restrict'));
        $mform->settype('text', PARAM_NOTAGS);
        $mform->addRule('ipend', get_string('required'), 'required', null, 'client');



        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('add_btn', 'local_restrict'));

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);





    }


    function validation($data, $files)
    {
        return array();
    }

}
