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


function stopProcess()
{
    exit;
}




$ipsArr = array();
$users = array();
$countDevices = 0;

$courseId = required_param('courseId', PARAM_TEXT);

$labs = required_param_array('labs', PARAM_TEXT);

//$quiz = required_param('quiz', PARAM_TEXT) ?? null;

//$quiz = required_param('quiz', PARAM_TEXT);


$quiz = null;



$quizes = getCourseQuizes($courseId);
$i = 0;
foreach ($quizes as $quiz) {

    //$sql_check = "SELECT * FROM {local_secureaccess_user_exam} WHERE ";
    $users[$i] = getUserEnrolled($courseId, $quiz->id);
    $i++;

}


if ($quiz != null) {

} else {
    $users[0] = getUserEnrolled($courseId, $quiz);
}

$j = 0;




list($in_sql, $in_params) = $DB->get_in_or_equal($labs, SQL_PARAMS_QM, 'param', true);
$sql_labs = 'SELECT id from {local_secureaccess_devices}
WHERE status = 1 AND labid ' . $in_sql;





/* -------------- CHECK IF Lab has an exam in the same time -------------------- */
$sql_check_time = "SELECT DISTINCT q.*
FROM {local_secureaccess_user_exam} ue
JOIN {quiz} q ON ue.examid = q.id
JOIN {local_secureaccess_devices} d ON ue.privateip = d.id
JOIN {local_secureaccess_labs} l ON d.labid = l.id
WHERE l.id $in_sql
  AND q.timeopen > UNIX_TIMESTAMP() - 43200";



$checkLabTime = $DB->get_records_sql($sql_check_time, $in_params);

$quizTimeDevices = [];



if (!empty($checkLabTime)) {

    foreach ($checkLabTime as $lab) {


        foreach ($quizes as $quiz) {
            // Get quiz data by ID (should return one record)
            $quizData = $DB->get_record('quiz', ['id' => $quiz->id]);


            if (!$quizData) {
                continue; // Skip if quiz not found
            }

            // Check if quiz open time falls within the lab's time range
            if ($quizData->timeopen >= $lab->timeopen && $quizData->timeopen < $lab->timeclose) {


                // Get related devices for this quiz from local_secureaccess_user_exam
                $quizTimeDevices = $DB->get_records_sql(
                    "SELECT privateip FROM {local_secureaccess_user_exam} WHERE examid = ?",
                    [$lab->id]
                );



            }
        }
    }
}


/* ----------------------------- END CHECK  ------------------------------- */






$ipsArr = $DB->get_records_sql($sql_labs, $in_params);

$sql_admin_devices = "SELECT device_id from {local_secureaccess_admin_devices}";

$adminIpArr = $DB->get_records_sql($sql_admin_devices);


// 2. Extract admin device IDs into a simple array
$adminIds = array_map(function ($item): mixed {
    return $item->device_id;
}, $adminIpArr);




// 3. Filter $ipsArr to remove devices that are in $adminIds
$filteredIpsArr = array_filter($ipsArr, function ($item) use ($adminIds) {
    return !in_array($item->id, haystack: $adminIds);
});


if (!empty($quizTimeDevices)) {

    $quizDeviceIps = array_map(function ($d) {
        return $d->privateip;
    }, $quizTimeDevices);

    echo json_encode(['message' => $quizDeviceIps]);

    $lastFilteredIps = array_filter($filteredIpsArr, function ($item) use ($quizDeviceIps) {
        return !in_array($item->id, $quizDeviceIps);
    });


    $filteredIpsArr = array_values($lastFilteredIps);


}

$filteredIpsArr = array_values($filteredIpsArr);




//echo json_encode(['message'=> $filteredIpsArr]);

// Now $filteredIpsArr contains devices NOT in admin list
$countDevices = count($filteredIpsArr);



$countUsers = 0;
$max = 0;
foreach ($users as $group) {
    $countUsers = count($group);
    if ($max < $countUsers) {
        $max = $countUsers;

    }
}

//echo json_encode(['status' => 1,'message' => 'devices: '.$countDevices."\n Users: ".$countUsers]);


//GET max quiz number --



$resultArr = [];
if ($countDevices < $max) {
    echo json_encode(['status' => 0, 'message' => get_string('distrputed_err','local_secureaccess') . $countDevices . "\n Users: " . $max]);
    stopProcess();
} else {

    for ($x = 0; $x < count($quizes); $x++) {
        $countUserInQuiz = count($users[$x]);
        $flawless = $countUserInQuiz - 1;
        $flat_users = array_values($users[$x]);

        // Shuffle IPs for random assignment
        $availableIps = array_values($filteredIpsArr); // make a copy
        shuffle($availableIps);


        //echo $countUserInQuiz;
        foreach ($availableIps as $index => $ip) {
            if ($flawless < 0) {
                break;
            }

            $record = new stdClass();
            $record->userid = $flat_users[$flawless]->user_id;
            $record->examid = $flat_users[$flawless]->quiz_id;
            $record->groupid = $flat_users[$flawless]->group_id;
            $record->status_id = 1;
            $record->privateip = $ip->id;

            if (!$record->groupid) {
                $flawless--;
                continue;
            }

            try {

                $DB->insert_record('local_secureaccess_user_exam', $record);
                $resultArr[] = ['message' => get_string('distrputed_sucess','local_secureaccess')];

                // Remove used IP so it's not reused
                unset($availableIps[$index]);

            } catch (Exception $e) {
                $resultArr[] = [
                    'message' => 'User ' . $record->userid . ' already distributed',
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
            }
            $flawless--;
        }
    }

    echo json_encode(['status' => 1, 'message' => get_string('distrputed_sucess','local_secureaccess')]);

}
















function getCourseQuizes($courseId)
{

    global $DB;

    $quizesId = array();

    $quizesId = $DB->get_records_sql('SELECT id FROM {quiz}
    WHERE course = ? and timeopen > UNIX_TIMESTAMP()', [$courseId]);



    return $quizesId;

}

function getUserEnrolled($courseid, $quizId)
{

    global $DB;
    $moduleQuiz = $DB->get_field('modules', 'id', ['name' => 'quiz']);

    $groups = $DB->get_field('course_modules', 'availability', [
        'course' => (int) $courseid,
        'module' => (int) $moduleQuiz,
        'instance' => (int) $quizId
    ]);


    $groupids = [];

    if ($groups) {
        $group = json_decode($groups);

        if (isset($group->c) && is_array($group->c)) {
            foreach ($group->c as $condition) {
                if ($condition->type === 'group' && isset($condition->id)) {
                    $groupids[] = (int) $condition->id;
                }
            }
        }
    }




    if ($groupids) {

        list($in_sql, $in_params) = $DB->get_in_or_equal($groupids, SQL_PARAMS_QM, 'param', true);

        $sql = 'SELECT
        u.id AS user_id,
        q.id AS quiz_id,
        gm.groupid AS group_id
        FROM {user} u
        JOIN {user_enrolments} ue ON ue.userid = u.id AND u.username > 20000
        JOIN {enrol} e ON e.id = ue.enrolid
        JOIN {course} c ON c.id = e.courseid
        JOIN {quiz} q ON q.course = c.id
        JOIN {groups_members} gm ON gm.userid = u.id
        WHERE c.id = ? AND q.id = ? and q.id != 86
        AND gm.groupid ' . $in_sql . '
        ORDER BY u.id';

        $params = array_merge([(int) $courseid, (int) $quizId], $in_params);
        $users = $DB->get_records_sql($sql, $params);

        return $users;


    } else {

        // ----------- NO RESTRICTION ------------------
        $users = $DB->get_records_sql('SELECT distinct
            u.id AS user_id,
            q.id AS quiz_id,
            gm.groupid AS group_id
            FROM mdl_user u
            JOIN mdl_course c on c.id = ? AND u.username > 20000
            JOIN mdl_quiz q ON q.course = c.id
            JOIN mdl_groups g ON g.courseid = c.id
            JOIN mdl_groups_members gm ON gm.userid = u.id AND gm.groupid= g.id
            WHERE q.id = ? and q.id != 86
            ORDER BY u.id
        ', [
            (int) $courseid,
            (int) $quizId,

        ]);

        return $users;

    }


}



/*



$courseExist = $DB->record_exists('course', ['id' => $courseId]);

if ($courseExist) {

}*/


die();

