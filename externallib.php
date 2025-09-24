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

require_once($CFG->libdir . '/externallib.php');

class local_restrict_external extends external_api
{

    //------ Contructer -----------
    public function __construct()
    {
        require_login();
        require_sesskey();

        if (!is_siteadmin()) {
            throw new required_capability_exception(
                context_system::instance(),
                'moodle/site:config',
                'nopermissions',
                ''
            );
        }
    }


    // ---------------------- PARAMETERS -------------------------



    // --------------------- STOP PROCESS PARAMETERS --------------
    public static function stop_process_parameters()
    {
        return new external_function_parameters([
            'error' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Lab IDs'),
                new external_value(PARAM_TEXT, 'Descripe')
            ),
        ]);
    }


    // ---------------- PARAMETERS For Distrpution ----------------
    public static function get_users_parameters()
    {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'labs' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Lab IDs')
            ),
            'quiz' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Quiz IDs'),
                'List of quiz IDs',
                VALUE_DEFAULT,
                []
            )
        ]);
    }

    // ---------------- PARAMETERS For Update Labs ----------------
    public static function update_labs_parameters()
    {
        return new external_function_parameters([
            'deviceid' => new external_value(PARAM_INT, 'Device ID'),
            'action' => new external_value(PARAM_TEXT, 'Action'),
            'dataaction' => new external_value(PARAM_TEXT, 'Data action', VALUE_DEFAULT, ''),
            'lab' => new external_value(PARAM_INT, 'Lab ID', VALUE_DEFAULT, 0)
        ]);
    }

    // ---------------- PARAMETERS For Quizes ----------------
    public static function get_quizes_parameters()
    {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID')
        ]);
    }

    // ---------------- PARAMETERS For Quizes ----------------
    public static function get_groups_parameters()
    {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID')
        ]);
    }

    // ---------------- PARAMETERS For Courses Records ----------------
    public static function courses_records_parameters()
    {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course Id'),
            'action' => new external_value(PARAM_TEXT, 'Action')

        ]);
    }



    // ---------------------- END PARAMETERS -------------------------



    //Stop To avoids DDL
    public static function stop_process($err)
    {
        $params = self::validate_parameters(self::stop_process_parameters(), [
            'error' => $err
        ]);
        $returns_value = $params['error'];
        return $returns_value;
    }
    // ---------------- User Distrpute Function ----------------
    public static function get_users($courseid, $labs = [], $out_quiz = [], $groups = [])
    {
        global $DB;






        $params = self::validate_parameters(self::get_users_parameters(), [
            'courseid' => $courseid,
            'labs' => $labs,
            'quiz' => $out_quiz,

        ]);

        $labs = $params['labs'];
        $courseId = $params['courseid'];


        //Check if user post quizes IDs (Optional)
        if (!empty($params['quiz'])) {
            foreach ($params['quiz'] as $quiz) {
                $quizes[] = (object) ['id' => $quiz];
            }
        } else {
            $quizes = self::get_course_quizes($courseId);
        }



        $ips_array = array();
        $users = array();
        $count_devices = 0;

        $i = 0;
        foreach ($quizes as $quiz) {
            $users[$i] = self::get_user_enrolled($courseId, $quiz->id);
            $i++;
        }



        list($in_sql, $in_params) = $DB->get_in_or_equal($labs, SQL_PARAMS_QM, 'param', true);
        $sql_labs = 'SELECT id from {local_restrict_devices}
WHERE status = 1 AND labid ' . $in_sql;





        /* -------------- CHECK IF Lab has an exam in the same time -------------------- */
        $sql_check_time = "SELECT DISTINCT q.*
FROM {local_restrict_user_exam} ue
JOIN {quiz} q ON ue.examid = q.id
JOIN {local_restrict_devices} d ON ue.privateip = d.id
JOIN {local_restrict_labs} l ON d.labid = l.id
WHERE l.id $in_sql
  AND q.timeopen > UNIX_TIMESTAMP() - 43200";



        $check_lab_time = $DB->get_records_sql($sql_check_time, $in_params);

        $quiz_time_devices = [];



        if (!empty($check_lab_time)) {

            foreach ($check_lab_time as $lab) {


                foreach ($quizes as $quiz) {
                    // Get quiz data by ID (should return one record)
                    $quiz_data = $DB->get_record('quiz', ['id' => $quiz->id]);


                    if (!$quiz_data) {
                        continue; // Skip if quiz not found
                    }

                    // Check if quiz open time falls within the lab's time range
                    if ($quiz_data->timeopen >= $lab->timeopen && $quiz_data->timeopen < $lab->timeclose) {


                        // Get related devices for this quiz from local_restrict_user_exam
                        $quiz_time_devices = $DB->get_records_sql(
                            "SELECT privateip FROM {local_restrict_user_exam} WHERE examid = ?",
                            [$lab->id]
                        );



                    }
                }
            }
        }


        /* ----------------------------- END CHECK  ------------------------------- */






        $ips_array = $DB->get_records_sql($sql_labs, $in_params);

        $sql_admin_devices = "SELECT device_id from {local_restrict_admin_devices}";

        $admin_ip_array = $DB->get_records_sql($sql_admin_devices);


        // 2. Extract admin device IDs into a simple array
        $admin_ids = array_map(function ($item): mixed {
            return $item->device_id;
        }, $admin_ip_array);




        // 3. Filter $ips_array to remove devices that are in Admin devices
        $filtered_ips_arr = array_filter($ips_array, function ($item) use ($admin_ids) {
            return !in_array($item->id, haystack: $admin_ids);
        });



        if (!empty($quiz_time_devices)) {
            // 4. Filter $ips_array to remove devices that are in same time of else exam
            $quiz_device_ips = array_map(function ($d) {
                return $d->privateip;
            }, $quiz_time_devices);



            $last_filtered_ips = array_filter($filtered_ips_arr, callback: function ($item) use ($quiz_device_ips) {
                return !in_array($item->id, $quiz_device_ips);
            });


            $filtered_ips_arr = array_values($last_filtered_ips);


        }
        // 5. convert $ips_array to array values to prepare to shuffle
        $filtered_ips_arr = array_values($filtered_ips_arr);






        // Now $filtered_ips_arr contains devices NOT in admin list
        $count_devices = count($filtered_ips_arr);



        $count_users = 0;
        $max = 0;
        foreach ($users as $group) {
            $count_users = count($group);
            if ($max < $count_users) {
                $max = $count_users;

            }
        }




        //GET max quiz number --
        if ($count_devices < $max) {
            return ['status' => 0, 'message' => get_string('distrputed_err', 'local_restrict') . $count_devices . "\n Users: " . $max];
        } else {

            // Preapre Distrputed -------
            for ($x = 0; $x < count($quizes); $x++) {

                $count_user_in_quiz = count($users[$x]);
                $flawless = $count_user_in_quiz - 1;
                $flat_users = array_values($users[$x]);

                // Shuffle IPs for random assignment
                $available_ips = array_values($filtered_ips_arr); // make a copy
                shuffle($available_ips);




                //Start users Insetion
                foreach ($available_ips as $index => $ip) {
                    if ($flawless < 0) {
                        break;
                    }
                    //$ip = array_shift($available_ips);

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

                        // Inserted Records
                        $DB->insert_record('local_restrict_user_exam', $record, false);

                        // Remove used IP so it's not reused
                        unset($available_ips[$index]);


                    } catch (Exception $e) {
                        $distrputed = false;

                        $err = $e->getTraceAsString();

                    }
                    $flawless--;
                }
            }

            return ['status' => 1, 'message' => get_string('distrputed_sucess', 'local_restrict')];



        }

    }

    //Get all quizes in selected course
    public static function get_course_quizes($courseId)
    {

        global $DB;

        $quizesId = array();

        $quizesId = $DB->get_records_sql('SELECT id FROM {quiz}
            WHERE course = ? and timeopen > UNIX_TIMESTAMP()', [$courseId]);



        return $quizesId;

    }

    public static function get_user_enrolled($courseid, $quizId)
    {

        global $DB;
        $module_quiz = $DB->get_field('modules', 'id', ['name' => 'quiz']);

         $groups = $DB->get_field('course_modules', 'availability', [
            'course' => (int) $courseid,
            'module' => (int) $module_quiz,
            'instance' => (int) $quizId
        ]);



        $groupids = [];


        // Get groupid in quiz groups restriction
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
            // Get all user has a quiz in course selected by group id
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
        WHERE c.id = ? AND q.id = ?
        AND gm.groupid ' . $in_sql . '
        ORDER BY u.id';

            $params = array_merge([(int) $courseid, (int) $quizId], $in_params);
            $users = $DB->get_records_sql($sql, $params);

            return $users;


        } else {

            // ----------- NO RESTRICTION ------------------

            // Get all user has a quiz in course selected
            $users = $DB->get_records_sql('SELECT distinct
            u.id AS user_id,
            q.id AS quiz_id,
            gm.groupid AS group_id
            FROM mdl_user u
            JOIN mdl_course c on c.id = ? AND u.username > 20000
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
    // --------------- END User Distrpute -----------



    //---------- Update Labs --------------------

    public static function update_labs($deviceid, $action, $dataaction = '', $lab = 0)
    {

        global $DB;




        $params = self::validate_parameters(self::update_labs_parameters(), [
            'action' => $action,
            'deviceid' => $deviceid,
            'dataaction' => $dataaction,
            'lab' => $lab
        ]);



        $deviceid = $params['deviceid'];
        $action = $params['action'];
        $dataaction = $params['dataaction'];
        $lab = $params['lab'];




        //Suspend devices in lab
        if ($action == 'sus') {
            $record = new stdClass();
            $record->id = $deviceid;
            $record->status = 0;

            try {
                $DB->update_record('local_restrict_devices', $record);
                return [
                    'status' => 1,
                    'message' => 'Success'
                ];
            } catch (dml_write_exception $e) {
                return [
                    'status' => 0,
                    'message' => 'Database write error: ' . $e->getMessage()
                ];
            }
        }

        //Active suspended devices in lab
        if ($action == 'act') {

            $record = new stdClass();
            $record->id = $deviceid;
            $record->status = 1;

            try {
                $DB->update_record('local_restrict_devices', $record);
                return [
                    'status' => 1,
                    'message' => 'Success'
                ];
            } catch (dml_write_exception $e) {
                return [
                    'status' => 0,
                    'message' => 'Database write error: ' . $e->getMessage()
                ];
            }
        }

        // Delete device from DB
        if ($action == 'del') {

            $record = new stdClass();
            $record->id = $deviceid;

            try {
                $DB->delete_records('local_restrict_devices', array('id' => $record->id));
                return [
                    'status' => 1,
                    'message' => 'Success'
                ];
            } catch (dml_write_exception $e) {
                return [
                    'status' => 0,
                    'message' => 'Database write error: ' . $e->getMessage()
                ];
            }
        }


        // Device add or remove from admin table
        if ($action == 'admin') {

            // Remove admin device
            if ($dataaction == "rmadmin") {
                $record = new stdClass();
                $record->labid = $lab;
                $record->device_id = $deviceid;
                try {
                    $DB->delete_records(
                        'local_restrict_admin_devices',
                        ['labid' => $lab, 'device_id' => $deviceid]
                    );
                    return ['status' => 1, 'message' => get_string('record_success', 'local_restrict')];
                } catch (dml_write_exception $e) {
                    return [
                        'status' => 0,
                        'message' => 'Database write error: ' . $e->getMessage()
                    ];
                }

            }
            // Add admin device
            else {

                $records = new stdClass();
                $records->labid = $lab;
                $records->device_id = $deviceid;

                try {

                    $DB->insert_record('local_restrict_admin_devices', $records, false);
                    return [
                        'status' => 1,
                        'message' => get_string('record_success', 'local_restrict'),
                    ];
                } catch (dml_write_exception $e) {
                    error_log('Record: ' . var_export($records, true));
                    return [
                        'status' => 0,
                        'message' => 'Database write error: ' . $e->getMessage()
                    ];
                }
            }


        }

        return ['status' => 0, 'message' => 'Unknown action'];


    }

    //---------- END Update Labs --------------------

    //---------- Quizes To Display --------------------

    public static function get_quizes($courseid)
    {

        $params = self::validate_parameters(self::get_quizes_parameters(), [
            'courseid' => $courseid,
        ]);

        $courseid = $params['courseid'];

        global $DB;

        $quizes_arr = array();

        $quizes = $DB->get_records_sql('SELECT * FROM {quiz}
        WHERE course = ?', [$courseid]);

        foreach ($quizes as $quiz) {
            $quizes_arr[] = [
                'id' => $quiz->id,
                'name' => $quiz->name
            ];
        }

        $quizes_list = array_values($quizes);
        if (!empty($quizes_list)) {
            return ['status' => 1, 'message' => $quizes_arr];
        } else {
            $quizes_list[] = [
                'id' => 0,
                'name' => 'No Quizes'
            ];
            return ['status' => 0, 'message' => $quizes_list];
        }

    }


    //---------- Groups To Display --------------------

    public static function get_groups($courseid)
    {

        $params = self::validate_parameters(self::get_groups_parameters(), [
            'courseid' => $courseid,
        ]);

        $courseid = $params['courseid'];

        global $DB;

        $groups_arr = array();

        $groups = $DB->get_records_sql('SELECT * FROM {groups}
        WHERE courseid = ?', [$courseid]);

        foreach ($groups as $group) {
            $groups_arr[] = [
                'id' => $group->id,
                'name' => $group->name
            ];
        }

        $groups_list = array_values($groups);
        if (!empty($groups_list)) {
            return ['status' => 1, 'message' => $groups_arr];
        } else {
            $groups_list[] = [
                'id' => 0,
                'name' => 'No Quizes'
            ];
            return ['status' => 0, 'message' => $groups_arr];
        }

    }
    //---------- END Groups To Display --------------------

    //---------- Delete Courses Records --------------------
    public static function courses_records($courseid, $action)
    {


        // Delete all course exams records to re-distrpute for technical error,
        // Or no need this data any more.


        global $DB;


        $params = self::validate_parameters(self::courses_records_parameters(), [
            'courseid' => $courseid,
            'action' => $action
        ]);


        $courseId = $params['courseid'];

        $action = $params['action'];

        $record = new stdClass();
        $record->id = $courseId;
        $record->status = 1;




        if ($action == 'del') {

            $sql = "SELECT ue.examid
        FROM {local_restrict_user_exam} ue
        JOIN {local_restrict_devices} d ON ue.privateip = d.id
        JOIN {local_restrict_labs} l ON d.labid = l.id
        JOIN {quiz} q on ue.examid = q.id
        JOIN {course} c on q.course = c.id AND c.id = ?
        JOIN {groups} g on g.id = ue.groupid
        JOIN {user} u
        where u.id = ue.userid AND u.username > 20000
        GROUP BY ue.examid";

            $sql_param = [$courseid];

            $rows = $DB->get_records_sql($sql, $sql_param);

            foreach ($rows as $row) {
                try {
                    $DB->delete_records('local_restrict_user_exam', ['examid' => (int) $row->examid]);
                } catch (dml_write_exception $e) {

                    return [
                        'status' => 0,
                        'message' => 'Database write error: ' . $row->examid
                    ];
                }
            }

            return ['status' => 1, 'message' => 'Success'];
        }
    }

    //---------- END Delete Courses Records --------------------



    // ---------------------- Returns -------------------------

    // ---------------------- Update Labs Returns -------------------------
    public static function update_labs_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_INT, 'Operation status (1 = success, 0 = failure)'),
            'message' => new external_value(PARAM_TEXT, 'Message describing the result'),

        ]);
    }


    //--------------------- Get Users Returns -------------------
    public static function get_users_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_INT, '0 = error, 1 = success'),
            'message' => new external_value(PARAM_TEXT, 'Result message'),
            'results' => new external_multiple_structure(
                new external_value(PARAM_RAW, 'Details of distribution'),
                'Optional result details',
                VALUE_OPTIONAL
            )
        ]);
    }

    //--------------------- Get Quizes Returns -------------------
    public static function get_quizes_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_INT, '0 = error, 1 = success'),
            'message' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Quiz ID'),
                    'name' => new external_value(PARAM_TEXT, 'Quiz name'),

                ])
            )
        ]);
    }

    //--------------------- Get Groups Returns -------------------
    public static function get_groups_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_INT, '0 = error, 1 = success'),
            'message' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Quiz ID'),
                    'name' => new external_value(PARAM_TEXT, 'Quiz name'),

                ])
            )
        ]);
    }


    //------------ Course Records Rerturns --------------
    public static function courses_records_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_INT, '0 = error, 1 = success'),
            'message' => new external_value(PARAM_TEXT, 'Message describing the result'),
        ]);
    }

    //------------ STOP PROCESS RETURNS -----------------
    public static function stop_process_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_INT, '0 = error, 1 = success'),
            'message' => new external_multiple_structure(PARAM_TEXT, 'Descripe data')
        ]);
    }



}
