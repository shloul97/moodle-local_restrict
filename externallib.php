<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

class local_secureaccess_external extends external_api
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


    // ---------------- PARAMETERS For Distrpution ----------------
    public static function get_users_parameters()
    {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'labs' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Lab IDs')
            ),
        ]);
    }

    // ---------------- PARAMETERS For Distrpution ----------------
    public static function update_labs_parameters()
    {
        return new external_function_parameters([
            'deviceid' => new external_value(PARAM_INT, 'Device ID'),
            'action' => new external_value(PARAM_TEXT, 'Action'),
            'dataaction' => new external_value(PARAM_TEXT, 'Data action', VALUE_DEFAULT, ''),
            'lab' => new external_value(PARAM_INT, 'Lab ID', VALUE_DEFAULT, 0)
        ]);
    }

    public static function get_quizes_parameters()
    {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Device ID')
        ]);
    }



    // ---------------------- END PARAMETERS -------------------------



    // ---------------- User Distrpute Function ----------------
    public static function get_users($courseid, $labs)
    {
        global $DB;

        $distrputed = false;
        $err = '';

        $params = self::validate_parameters(self::get_users_parameters(), [
            'courseid' => $courseid,
            'labs' => $labs,
        ]);

        $labs = $params['labs'];
        $courseId = $params['courseid'];




        function stop_process()
        {
            exit;
        }


        $ipsArr = array();
        $users = array();
        $countDevices = 0;





        $quiz = null;



        $quizes = self::get_course_quizes($courseId);
        $i = 0;
        foreach ($quizes as $quiz) {

            $users[$i] = self::get_user_enrolled($courseId, $quiz->id);
            $i++;

        }


        if ($quiz != null) {

        } else {
            $users[0] = self::get_user_enrolled($courseId, $quiz);
        }



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




        // 3. Filter $ipsArr to remove devices that are in Admin devices
        $filteredIpsArr = array_filter($ipsArr, function ($item) use ($adminIds) {
            return !in_array($item->id, haystack: $adminIds);
        });



        if (!empty($quizTimeDevices)) {
            // 4. Filter $ipsArr to remove devices that are in same time of else exam
            $quizDeviceIps = array_map(function ($d) {
                return $d->privateip;
            }, $quizTimeDevices);



            $lastFilteredIps = array_filter($filteredIpsArr, function ($item) use ($quizDeviceIps) {
                return !in_array($item->id, $quizDeviceIps);
            });


            $filteredIpsArr = array_values($lastFilteredIps);


        }
        // 5. convert $ipsArr to array values to prepare to shuffle
        $filteredIpsArr = array_values($filteredIpsArr);






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




        //GET max quiz number --
        if ($countDevices < $max) {
            return ['status' => 0, 'message' => get_string('distrputed_err', 'local_secureaccess') . $countDevices . "\n Users: " . $max];
            self::stop_process();
        } else {

            // Preapre Distrputed -------
            for ($x = 0; $x < count($quizes); $x++) {

                $countUserInQuiz = count($users[$x]);
                $flawless = $countUserInQuiz - 1;
                $flat_users = array_values($users[$x]);

                // Shuffle IPs for random assignment
                $availableIps = array_values($filteredIpsArr); // make a copy
                shuffle($availableIps);




                //Start users Insetion
                foreach ($availableIps as $index => $ip) {
                    if ($flawless < 0) {
                        break;
                    }
                    //$ip = array_shift($availableIps);

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
                        $DB->insert_record('local_secureaccess_user_exam', $record, false);

                        // Remove used IP so it's not reused
                        unset($availableIps[$index]);


                    } catch (Exception $e) {
                        $distrputed = false;

                        $err = $e->getTraceAsString();

                        return ['status' => 0, 'message' => 'error : ' . $e->getTraceAsString()];

                    }
                    $flawless--;
                }
            }

            return ['status' => 1, 'message' => get_string('distrputed_sucess', 'local_secureaccess')];



        }

    }

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
        WHERE c.id = ? AND q.id = ?
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
                $DB->update_record('local_secureaccess_devices', $record);
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
                $DB->update_record('local_secureaccess_devices', $record);
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

        if ($action == 'del') {

            $record = new stdClass();
            $record->id = $deviceid;

            try {
                $DB->delete_records('local_secureaccess_devices', array('id' => $record->id));
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

        if ($action == 'admin') {

            if ($dataaction == "rmadmin") {
                $record = new stdClass();
                $record->labid = $lab;
                $record->device_id = $deviceid;
                try {
                    $DB->delete_records(
                        'local_secureaccess_admin_devices',
                        ['labid' => $lab, 'device_id' => $deviceid]
                    );
                    return ['status' => 1, 'message' => get_string('record_success', 'local_secureaccess')];
                } catch (dml_write_exception $e) {
                    return [
                        'status' => 0,
                        'message' => 'Database write error: ' . $e->getMessage()
                    ];
                }

            } else {

                $records = new stdClass();
                $records->labid = $lab;
                $records->device_id = $deviceid;

                try {

                    $DB->insert_record('local_secureaccess_admin_devices', $records, false);
                    return [
                        'status' => 1,
                        'message' => get_string('record_success', 'local_secureaccess'),
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
    //---------- END Quizes To Display --------------------

    //---------- Delete Courses Records --------------------
    public static function courses_records()
    {


        global $DB;

        $courseId = required_param('courseId', PARAM_INT);

        $action = required_param('action', PARAM_TEXT);

        $record = new stdClass();
        $record->id = $courseId;
        $record->status = 1;




        if ($action == 'del') {

            $rows = $DB->get_records_sql('SELECT ue.examid
        FROM mdl_local_secureaccess_user_exam ue
        JOIN mdl_local_secureaccess_devices d ON ue.privateip = d.id
        JOIN mdl_local_secureaccess_labs l ON d.labid = l.id
        JOIN mdl_quiz q on ue.examid = q.id
        JOIN mdl_course c on q.course = c.id AND c.id = ?
        JOIN mdl_groups g on g.id = ue.groupid
        JOIN mdl_user u where u.id = ue.userid AND u.username > 20000
        group by ue.examid');

            return json_encode(['data' => $rows]);
            foreach ($rows as $id) {

                $DB->delete_records('local_secureaccess_user_exam', ['examid' => (int) $id->examid]);
            }

            return json_encode(array('status' => 1, 'message' => 'Success', 'data' => 'dataTest_courses_records'));
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

    //--------------------- Get Users Returns -------------------
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



}
