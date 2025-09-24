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



require_once('../../config.php');
require_once($CFG->dirroot . '/local/restrict/classes/form/inquiry_form.php');

require_login();

// Restrict access to admins only
if (!is_siteadmin()) {
    throw new required_capability_exception(
        context_system::instance(),
        'moodle/site:config',
        'nopermissions',
        ''
    );
}

// Setup page
$PAGE->set_context(context_system::instance());

global $OUTPUT, $PAGE, $CFG;

$mform = new inquiry();



$PAGE->set_url(new moodle_url("/local/restrict/inquiry.php"));

$PAGE->set_pagelayout('admin');
$PAGE->set_title("Secure Exam Access | inqury");


$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js_call_amd('local_restrict/courses', 'init');




if ($mform->is_cancelled()) {



} else if ($fromform = $mform->get_data()) {






    $lab_ips = $DB->get_records_select('local_restrict_devices', 'labid = ?', [1]);

    $record = new stdClass();
    $record->course = $fromform->course;

    $course_exams = $DB->get_records_select('quiz', 'course = ?', [$record->course], 2);












    try {



    } catch (Exception $e) {
        \core\notification::error("Failed to insert the lab.");
    }


}else{

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


    $options = array(); // Blank value with a prompt

    foreach ($records as $choice) {
        array_push($options, [
            'courseid' => $choice->id,
            'idnumber' => $choice->idnumber,
            'coursename' => $choice->fullname
        ]);
    }

    $data1 = [
        'table' => [
            'tableData' => $options
        ]
    ];


}















$header = [

    "insertGroup" => new moodle_url("/local/restrict/insert_groups.php"),
    "insertLabs" => new moodle_url("/local/restrict/insert_labs.php"),
    "insertIp" => new moodle_url("/local/restrict/insert_ranges.php"),
    "updateLabs" => new moodle_url("/local/restrict/update_labs.php"),
    "inquiry" => new moodle_url("/local/restrict/inquiry.php"),
    "home" => new moodle_url("/local/restrict/index.php")
];



$context = [

    'header' => $header,
    'table' => $data1['table'],
    'sesskey' => sesskey()
];


echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_restrict/search', $context);
echo $OUTPUT->footer();

