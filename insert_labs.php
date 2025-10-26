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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/restrict/classes/form/insert_labs_form.php');

require_login();

// Restrict access to admins only.
if (!is_siteadmin()) {
    throw new required_capability_exception(
        context_system::instance(),
        'moodle/site:config',
        'nopermissions',
        ''
    );
}

// Setup page.
$PAGE->set_context(context_system::instance());

global $OUTPUT, $PAGE, $CFG;

$mform = new insert_labs();

$PAGE->set_url(new moodle_url("/local/restrict/add_labs.php"));
$PAGE->set_context(\context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title("Lab Insertion");

if ($mform->is_cancelled()) {
} else if ($fromform = $mform->get_data()) {
    $record = new stdClass();
    $record->lab_name = $fromform->labname;
    $record->capacity = $fromform->capacity;

    try {
        $id = $DB->insert_record('local_restrict_labs', $record);
        if ($id) {
            \core\notification::success(get_string('lab_insertion', 'local_restrict') . " $id");
        }
    } catch (Exception $e) {
        \core\notification::error(get_string('failed_lab_insertion', 'local_restrict'));
    }
}

$templatectxauto = [
    'submitted' => false,
    'formhtml' => $mform->render()
];

$header = [
    "insertGroup" => new moodle_url("/local/restrict/insert_groups.php"),
    "insertLabs" => new moodle_url("/local/restrict/insert_labs.php"),
    "insertIp" => new moodle_url("/local/restrict/insert_ranges.php"),
    "updateLabs" => new moodle_url("/local/restrict/update_labs.php"),
    "inquiry" => new moodle_url("/local/restrict/inquiry.php"),
    "home" => new moodle_url("/local/restrict/index.php")
];

$context = [
    'form' => [$templatectxauto],
    'header' => $header
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_restrict/container', $context);
echo $OUTPUT->footer();
