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



class insert_ranges_admin extends moodleform {

    protected function definition() {

        global $OUTPUT, $PAGE, $CFG, $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'adminHeader', get_string('admin_header', 'local_restrict'));
        $mform->setExpanded('adminHeader', false,); //



        $records = $DB->get_records('local_restrict_labs');




        $options = array('' => 'Select a lab'); // Blank value with a prompt

        foreach ($records as $choice) {
            $options[$choice->id] = $choice->lab_name;
        }


        $mform->addElement('select','labid',get_string('labname', 'local_restrict'),$options);
        $mform->setType('labid', PARAM_TEXT);
        $mform->addRule('labid', get_string('required'), 'required', null, 'client');


        $mform->addElement('textarea', 'admin_ips', get_string('admin_ips', 'local_restrict'), ['placeholder' => get_string('admin_ips_place', 'local_restrict'),'rows="10" cols="10"']);
        $mform->addRule('admin_ips', get_string('required'), 'required', null, 'client');



        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('add_btn', 'local_restrict'));

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);





    }


    function validation($data, $files)
    {
        return array();
    }

}
