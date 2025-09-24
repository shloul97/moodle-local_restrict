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

class add_user extends moodleform {

    protected function definition() {

        global $OUTPUT, $PAGE, $CFG;

        $mform = $this->_form;

        $mform->addElement('text','name',get_string('labname', 'local_restrict'));
        $mform->settype('text', PARAM_NOTAGS);

        $mform->addElement('text','ipstart',get_string('ipstart', 'local_restrict'));
        $mform->settype('text', PARAM_NOTAGS);

        $mform->addElement('text','ipend',get_string('ipend', 'local_restrict'));
        $mform->settype('text', PARAM_NOTAGS);

        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('add_btn', 'local_restrict'));

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

    }


    function validation($data, $files)
    {
        return array();
    }

}
