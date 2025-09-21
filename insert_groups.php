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
 * @package   local_secureaccess
 * @copyright 2025 Moayad Shloul <shloul97@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../config.php');
require_once($CFG->dirroot . '/local/secureaccess/classes/form/insert_groups_form.php');


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

$mform = new insert_groups();

$PAGE->set_url(new moodle_url("/local/secureaccess/insert_groups.php"));
$PAGE->set_context(\context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title("Add Users");


$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js_call_amd('local_secureaccess/selector', 'init');
$PAGE->requires->js_call_amd('local_secureaccess/distrpute', 'init');
//$PAGE->requires->js('/local/secureaccess/amd/src/distrpute2.js');




if ($mform->is_cancelled()) {



}
else if ($fromform = $mform->get_data()) {

    echo '<br><br><br><br>';
    echo '<br><br><br><br>';
    echo '<br><br><br><br>';


    var_dump($fromform);

    $labIps = $DB->get_records_select('local_secureaccess_devices','labid = ?',[1]);

    $record = new stdClass();
    $record->course = $fromform->course;

    $course_exams = $DB->get_records_select('quiz','course = ?',[$record->course],2);






    echo '<h1>course ID:' .$record->course.'</h1>';





    try
    {



    }
    catch(Exception $e)
    {
        \core\notification::error("Failed to insert the lab.");
    }


}



$templatecontext = [
    'submitted' => false,
    'formhtml' => $mform->render()
];



$header = [
    "insertGroup" => new moodle_url("/local/secureaccess/insert_groups.php"),
    "insertLabs"=> new moodle_url("/local/secureaccess/insert_labs.php"),
    "insertIp"=> new moodle_url("/local/secureaccess/insert_ranges.php"),
    "updateLabs"=> new moodle_url("/local/secureaccess/update_labs.php"),
    "inquiry"=> new moodle_url("/local/secureaccess/inquiry.php"),
    "home"=> new moodle_url("/local/secureaccess/index.php")
];


$context = [
    'header' => $header,
    'form' => [$templatecontext]
];

//$PAGE->requires->js_call_amd('local_secureaccess/selector', 'init');
//$PAGE->requires->js('/local/secureaccess/amd/src/selector.js');
echo $OUTPUT->header();




echo  $OUTPUT->notification('Course should be has a group and every group has an exam', \core\output\notification::NOTIFY_WARNING, false);


echo $OUTPUT->render_from_template('local_secureaccess/container',$context);







echo $OUTPUT->footer();

