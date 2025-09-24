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



class inquiry extends moodleform {

    protected function definition() {

        global $OUTPUT, $PAGE, $CFG,$DB;

        $mform = $this->_form;



        $records = $DB->get_records_sql('SELECT  c.id,c.idnumber, c.fullname
            FROM mdl_local_restrict_user_exam ue
            JOIN mdl_local_restrict_devices d ON ue.privateip = d.id
            JOIN mdl_local_restrict_labs l ON d.labid = l.id
            JOIN mdl_quiz q on ue.examid = q.id
            JOIN mdl_course c on q.course = c.id
            JOIN mdl_groups g on g.id = ue.groupid
            JOIN mdl_user u where u.id = ue.userid AND u.username > 20000
            group by c.idnumber
        ');


        $options = array('' => 'Select Course'); // Blank value with a prompt

        foreach ($records as $choice) {
            $options[$choice->id] = $choice->fullname . ' - ' . $choice->idnumber;
        }


        $mform->addElement('select','courseid',get_string('courses', 'local_restrict'),$options);
        $mform->setType('courseid', paramtype: PARAM_TEXT);





        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('filter', 'local_restrict'));

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

    }


    function validation($data, $files)
    {
        return array();
    }

}
