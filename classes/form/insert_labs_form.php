<?php
// This file is part of Moodle - http://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify.
// it under the terms of the GNU General Public License as published by.
// the Free Software Foundation, either version 3 of the License, or.
// This file is part of Moodle - http://moodle.org/.
// Moodle is distributed in the hope that it will be useful,.
// but WITHOUT ANY WARRANTY; without even the implied warranty of.
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the.
// This file is part of Moodle - http://moodle.org/.
// You should have received a copy of the GNU General Public License.
// This file is part of Moodle - http://moodle.org/.
/*
 * @package   local_restrict
 * @copyright 2025 Moayad Shloul <shloul97@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';



class insert_labs extends moodleform {


    protected function definition() {

        global $OUTPUT, $PAGE, $CFG;

        $mform = $this->_form;

        $mform->addElement('text', 'labname', get_string('labname', 'local_restrict'));
        $mform->settype('text', PARAM_NOTAGS);
        $mform->addRule('labname', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'capacity', get_string('capacity', 'local_restrict'));
        $mform->settype('text', PARAM_NOTAGS);

        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('add_btn', 'local_restrict'));

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

    }


    function validation($data, $files) {
        return array();
    }

}
