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

define('AJAX_SCRIPT', true);


require_once(__DIR__ . '/../../../../config.php');


//echo json_encode(array('status'=> 0,'message'=> 'error'));

require_login();
require_sesskey();


// Restrict access to admins only
if (!is_siteadmin()) {
    throw new required_capability_exception(
        context_system::instance(),
        'moodle/site:config',
        'nopermissions',
        ''
    );
}


$courseId = required_param('courseId', PARAM_INT);

$action = required_param('action', PARAM_TEXT);

$record = new stdClass();
$record->id = $courseId;
$record->status = 1;




if($action == 'del'){

    $rows = $DB->get_records_sql('SELECT ue.examid
        FROM mdl_local_secureaccess_user_exam ue
        JOIN mdl_local_secureaccess_devices d ON ue.privateip = d.id
        JOIN mdl_local_secureaccess_labs l ON d.labid = l.id
        JOIN mdl_quiz q on ue.examid = q.id
        JOIN mdl_course c on q.course = c.id AND c.id = ?
        JOIN mdl_groups g on g.id = ue.groupid
        JOIN mdl_user u where u.id = ue.userid AND u.username > 20000
        group by ue.examid');

    echo json_encode(['data' => $rows]);
    foreach($rows as $id){

        $DB->delete_records('local_secureaccess_user_exam', ['examid' => (int)$id->examid]);
    }

    echo json_encode(array('status'=> 1,'message'=> 'Success', 'data' => $options));
}






die();