<?php

/**
 *
 * @package local_restrict
 * @author Moayad Shloul
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




$ipsArr = array();
$users = array();
$adminDevices = array();
$countDevices = 0;

$courseId = required_param('courseId', PARAM_TEXT);

$labs = required_param_array('labs', PARAM_TEXT);





$quizes = getCourseQuizes($courseId);
$i = 0;
foreach($quizes as $quiz){
    $users[$i] = getUserEnrolled($courseId, $quiz->id);

    // $users[$i] =  $quiz;
    $i++;

}
$j = 0;


list($in_sql, $in_params) = $DB->get_in_or_equal($labs, SQL_PARAMS_QM, 'param', true);
$sql_admin = $DB->get_records('local_restrict_admin_devices');

$adminDevices = [];
foreach ($sql_admin as $record_admin) {
    $adminDevices[] = $record_admin->device_id;
}
if (empty($adminDevices)) {
    $adminDevices = [-1]; // Dummy ID that will never match
}

list($not_in_sql_admin, $not_in_params_admin) = $DB->get_in_or_equal($adminDevices, SQL_PARAMS_QM, 'admin', false);

$sql_labs = "SELECT id from {local_restrict_devices}
    WHERE labid $in_sql and status = 1 AND id $not_in_sql_admin";

$params = array_merge($in_params, $not_in_params_admin);

$ipsArr = $DB->get_records_sql($sql_labs, $params);
$countDevices = count($ipsArr);






for($x = 0; $x < count($quizes);$x++)
{
    $countUsers = count($users[$x]);
    $flawless = $countUsers-1;
    if($countDevices < $countUsers){
        echo json_encode(value: ['message' => 'Device out of range', 'Values' => $ipsArr]);
        break;
    }
    else{
        $flat_users = array_values($users[$x]);
        foreach($ipsArr as $ip){

            $record = new stdClass();
            $record->userid = $flat_users[$flawless]->user_id;
            $record->examid = $flat_users[$flawless]->quiz_id;
            $record->groupid = $flat_users[$flawless]->group_id;
            $record->privateip = $ip->id;

            //echo json_encode(['message' => "user ".$flat_users[$flawless]], JSON_PRETTY_PRINT);


            if($flawless < 0){
                break;
            }

            $flawless--;



            try{
                $DB->insert_record('local_restrict_user_exam', $record);

            }
            catch (Exception $e) {
                echo json_encode([
                    'message' => 'User ' . $record->userid . ' already distributed',
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ], JSON_PRETTY_PRINT);
            }

        }
    }

}





//$flat_users = array_values($users[0]);
//echo $users_test;
//echo json_encode(['message' => $flat_users[11]->user_id],JSON_PRETTY_PRINT);








function getCourseQuizes($courseId){

    global $DB;

    $quizesId = array();

    $quizesId = $DB->get_records_sql('SELECT id FROM {quiz}
    WHERE course = ?', [$courseId]);



    return $quizesId;

}

function getUserEnrolled($courseid, $quizId){

    global $DB;
    $moduleQuiz = $DB->get_field('modules', 'id', ['name' => 'quiz']);

    $groups = $DB->get_field('course_modules', 'availability', [
        'course' => (int)$courseid,
        'module' =>(int)$moduleQuiz,
        'instance'=> (int)$quizId
    ]);


    $groupids = [];

    if ($groups) {
        $group = json_decode($groups);

        if (isset($group->c) && is_array($group->c)) {
            foreach ($group->c as $condition) {
                if ($condition->type === 'group' && isset($condition->id)) {
                    $groupids[] = (int)$condition->id;
                }
            }
        }

    }




    if($groupids){

        list($in_sql, $in_params) = $DB->get_in_or_equal($groupids, SQL_PARAMS_QM, 'param', true);

        $sql = 'SELECT
        u.id AS user_id,
        q.id AS quiz_id,
        gm.groupid AS group_id
        FROM {user} u
        JOIN {user_enrolments} ue ON ue.userid = u.id
        JOIN {enrol} e ON e.id = ue.enrolid
        JOIN {course} c ON c.id = e.courseid
        JOIN {quiz} q ON q.course = c.id
        JOIN {groups_members} gm ON gm.userid = u.id
        WHERE c.id = ? AND q.id = ?
        AND gm.groupid '.$in_sql.'
        ORDER BY u.id';

        $params = array_merge([(int) $courseid, (int) $quizId], $in_params);
        $users = $DB->get_records_sql($sql, $params);

        return $users;


    }
    else{

        // ----------- NO RESTRICTION ------------------
        $users = $DB->get_records_sql('SELECT distinct
            u.id AS user_id,
            q.id AS quiz_id,
            gm.groupid AS group_id
            FROM mdl_user u
            JOIN mdl_course c on c.id = ?
            JOIN mdl_quiz q ON q.course = c.id
            JOIN mdl_groups g ON g.courseid = c.id
            JOIN mdl_groups_members gm ON gm.userid = u.id AND gm.groupid= g.id
            WHERE q.id = ?
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

