<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>
/**
 *
 * @package   local_restrict
 * @copyright 2025 Moayad Shloul <shloul97@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */


defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/externallib.php';

class local_restrict_external extends external_api
{


    // ---------------------- PARAMETERS -------------------------.

    // --------------------- STOP PROCESS PARAMETERS --------------.

    /**
     * Summary of stop_process_parameters
     *
     * @return external_function_parameters
     */
    public static function stop_process_parameters()
    {
        return new external_function_parameters(
            [
                'error' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Lab IDs'),
                    new external_value(PARAM_TEXT, 'Descripe')
                ),
            ]
        );
    }


    // ---------------- PARAMETERS For Distrpution ----------------.
    /**
     * Summary of get_users_parameters
     *
     * @return external_function_parameters
     */
    public static function get_users_parameters()
    {
        return new external_function_parameters(
            [
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
            ]
        );
    }

    // ---------------- PARAMETERS For Update Labs ----------------.
    /**
     * Summary of update_labs_parameters
     *
     * @return external_function_parameters
     */
    public static function update_labs_parameters()
    {
        return new external_function_parameters(
            [
                'deviceid' => new external_value(PARAM_INT, 'Device ID'),
                'action' => new external_value(PARAM_TEXT, 'Action'),
                'dataaction' => new external_value(PARAM_TEXT, 'Data action', VALUE_DEFAULT, ''),
                'lab' => new external_value(PARAM_INT, 'Lab ID', VALUE_DEFAULT, 0)
            ]
        );
    }

    // ---------------- PARAMETERS For Quizes ----------------.
    /**
     * Summary of get_quizes_parameters
     *
     * @return external_function_parameters
     */
    public static function get_quizes_parameters()
    {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'Course ID')
            ]
        );
    }

    // ---------------- PARAMETERS For Quizes ----------------.
    /**
     * Summary of get_groups_parameters
     *
     * @return external_function_parameters
     */
    public static function get_groups_parameters()
    {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'Course ID')
            ]
        );
    }

    // ---------------- PARAMETERS For Courses Records ----------------.
    /**
     * Summary of courses_records_parameters
     *
     * @return external_function_parameters
     */
    public static function courses_records_parameters()
    {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'Course Id'),
                'action' => new external_value(PARAM_TEXT, 'Action')

            ]
        );
    }

    // ---------------------- END PARAMETERS -------------------------.

    // Stop To avoids DDL.
    public static function stop_process($err)
    {
        $params = self::validate_parameters(
            self::stop_process_parameters(),
            [
                'error' => $err
            ]
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/restrict:manage', $context);

        $returnsvalue = $params['error'];
        return $returnsvalue;
    }

    // ---------------- User Distrpute Function ----------------.

    /**
     * Distributes users across devices in labs for selected quizzes.
     *
     * @param  int   $courseid The course ID.
     * @param  int[] $labs     List of lab IDs.
     * @param  int[] $out_quiz Optional quiz IDs (if empty, all course quizzes are used).
     * @return array Status and message of distribution.
     */
    public static function get_users($courseid, $labs = [], $outquiz = [])
    {
        global $DB;

        $params = self::validate_parameters(
            self::get_users_parameters(),
            [
                'courseid' => $courseid,
                'labs' => $labs,
                'quiz' => $outquiz,

            ]
        );

        // Use system context for global admin actions (or context for the lab's course).
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/restrict:manage', $context);

        $check_max_count = true;

        $labs = $params['labs'];
        $courseid = $params['courseid'];


        // Check if user post quizes IDs (Optional).
        if (!empty($params['quiz'])) {

            foreach ($params['quiz'] as $quiz) {
                $quizes[] = (object) ['id' => $quiz];
            }
        } else {
            $quizes = self::get_course_quizes($courseid);
        }

        $ipsarray = array();
        $users = array();
        $countdevices = 0;

        $i = 0;
        foreach ($quizes as $quiz) {
            $users[$i] = self::get_user_enrolled($courseid, $quiz->id);
            $i++;
        }

        list($insql, $inparams) = $DB->get_in_or_equal($labs, SQL_PARAMS_QM, 'param', true);
        $sqllabs = 'SELECT id from {local_restrict_devices}
        WHERE status = 1 AND labid ' . $insql;

        /* -------------- CHECK IF Lab has an exam in the same time -------------------- */
        $sqlchecktime = "SELECT DISTINCT q.*
        FROM {local_restrict_user_exam} ue
        JOIN {quiz} q ON ue.examid = q.id
        JOIN {local_restrict_devices} d ON ue.privateip = d.id
        JOIN {local_restrict_labs} l ON d.labid = l.id
        WHERE l.id $insql
        AND q.timeopen > UNIX_TIMESTAMP() - 43200";



        $checklabtime = $DB->get_records_sql($sqlchecktime, $inparams);

        $quiztimedevices = [];

        if (!empty($checklabtime)) {

            foreach ($checklabtime as $lab) {

                foreach ($quizes as $quiz) {
                    // Get quiz data by ID (should return one record).
                    $quizdata = $DB->get_record('quiz', ['id' => $quiz->id]);

                    if (!$quizdata) {
                        continue; // Skip if quiz not found.
                    }

                    // Check if quiz open time falls within the lab's time range.
                    if ($quizdata->timeopen >= $lab->timeopen && $quizdata->timeopen < $lab->timeclose) {

                        // Get related devices for this quiz from local_restrict_user_exam.
                        $devices = $DB->get_records_sql(
                            "SELECT privateip FROM {local_restrict_user_exam} WHERE examid = ?",
                            [$lab->id]
                        );

                        if (!empty($devices)) {
                            // Merge all found devices into one flat array
                            $quiztimedevices = array_merge($quiztimedevices, $devices);
                        }

                    }

                }
            }
        }



        /* ----------------------------- END CHECK  ------------------------------- */

        $ipsarray = $DB->get_records_sql($sqllabs, $inparams);

        $sqladmindevices = "SELECT device_id from {local_restrict_admin_devices}";

        $adminiparray = $DB->get_records_sql($sqladmindevices);

        // 2. Extract admin device IDs into a simple array.
        $adminids = array_map(
            function ($item) {
                return $item->device_id;
            },
            $adminiparray
        );

        // 3. Filter $ips_array to remove devices that are in Admin devices.
        $filteredipsarr = array_filter(
            $ipsarray,
            function ($item) use ($adminids) {
                return !in_array($item->id, $adminids);
            }
        );

        if (!empty($quiztimedevices)) {
            // 4. Filter $ips_array to remove devices that are in same time of else exam.
            $quizdeviceips = array_map(
                function ($d) {
                    return $d->privateip;
                },
                $quiztimedevices
            );

            $lastfilteredips = array_filter(
                $filteredipsarr,
                callback: function ($item) use ($quizdeviceips) {
                    return !in_array((int) $item->id, $quizdeviceips);
                }
            );

            $filteredipsarr = array_values($lastfilteredips);

        }
        // 5. convert $ips_array to array values to prepare to shuffle.
        $filteredipsarr = array_values($filteredipsarr);


        $teststr = '';
        foreach ($filteredipsarr as $param) {
            $teststr .= ', ' . $param->id;
        }



        // Now $filtered_ips_arr contains devices NOT in admin list.
        $countdevices = count($filteredipsarr);

        // Check users count.
        $count_users = 0;
        foreach ($users as $user) {
            $count_users += count($user);
        }

        // GET max quiz number --.
        if ((int) $countdevices < (int) $count_users) {
            return ['status' => 0, 'message' => get_string('distrputed_err', 'local_restrict') . ' ' . $countdevices . "\n Users: " . $count_users];
        } else {


            // Preapre Distrputed -------.
            for ($x = 0; $x < count($quizes); $x++) {

                $countuserinquiz = count($users[$x]);
                $flawless = $countuserinquiz - 1;
                $flatusers = array_values($users[$x]);

                // Shuffle IPs for random assignment.
                $availableips = array_values($filteredipsarr); // make a copy.
                shuffle($availableips);

                // Start users Insetion.
                foreach ($availableips as $index => $ip) {
                    if ($flawless < 0) {
                        break;
                    }
                    // $ip = array_shift($available_ips);.

                    $record = new stdClass();
                    $record->userid = $flatusers[$flawless]->user_id;
                    $record->examid = $flatusers[$flawless]->quiz_id;
                    $record->groupid = $flatusers[$flawless]->group_id;
                    $record->status_id = 1;
                    $record->privateip = $ip->id;

                    if (!$record->groupid) {
                        $flawless--;
                        continue;
                    }

                    try {

                        // Inserted Records.
                        $DB->insert_record('local_restrict_user_exam', $record, false);
                        // Remove used IP so it's not reused.
                        unset($availableips[$index]);

                    } catch (Exception $e) {
                        $distrputed = false;
                        $err = $e->getTraceAsString();

                    }
                    $flawless--;
                }
            }

            return ['status' => 1, 'message' => get_string('distrputed_sucess', 'local_restrict') . ' ' . $teststr];

        }

    }

    // Get all quizes in selected course.
    /**
     * Retrieves all quizzes in a course with an open time in the future.
     *
     * @param  int $courseId The course ID.
     * @return array List of quiz IDs.
     */
    public static function get_course_quizes($courseid)
    {

        global $DB;

        // Use system context for global admin actions.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/restrict:manage', $context);

        $quizesid = array();

        $quizesid = $DB->get_records_sql(
            'SELECT id FROM {quiz}
            WHERE course = ? and timeopen > UNIX_TIMESTAMP()',
            [$courseid]
        );

        return $quizesid;

    }

    /**
     * Retrieves enrolled users for a specific quiz.
     *
     * @param  int $courseid The course ID.
     * @param  int $quizId   The quiz ID.
     * @return array List of enrolled users with user_id, quiz_id, and group_id.
     */
    public static function get_user_enrolled($courseid, $quizid)
    {

        global $DB;

        // Use system context for global admin actions.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/restrict:manage', $context);

        $modulequiz = $DB->get_field('modules', 'id', ['name' => 'quiz']);

        $groups = $DB->get_field(
            'course_modules',
            'availability',
            [
                'course' => (int) $courseid,
                'module' => (int) $modulequiz,
                'instance' => (int) $quizid
            ]
        );
        $groupids = [];
        // Get groupid in quiz groups restriction.
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
            // Get all user has a quiz in course selected by group id.
            list($insql, $inparams) = $DB->get_in_or_equal($groupids, SQL_PARAMS_QM, 'param', true);

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
        AND gm.groupid ' . $insql . '
        AND u.id NOT IN (
        SELECT ra.userid
        FROM {role_assignments} ra
        JOIN {context} ctx ON ctx.id = ra.contextid
        JOIN {role} r ON r.id = ra.roleid
        WHERE ra.userid = u.id
            AND ctx.contextlevel = 50
            AND ctx.instanceid = c.id
            AND r.shortname IN (\'manager\',\'editingteacher\', \'teacher\')
        )
        ORDER BY u.id';

            $params = array_merge([(int) $courseid, (int) $quizid], $inparams);
            $users = $DB->get_records_sql($sql, $params);

            return $users;

        } else {

            // ----------- NO RESTRICTION ------------------.

            // Get all user has a quiz in course selected.
            $users = $DB->get_records_sql(
                'SELECT distinct
            u.id AS user_id,
            q.id AS quiz_id,
            gm.groupid AS group_id
            FROM {user} u
            JOIN {course} c on c.id = ?
            JOIN {quiz} q ON q.course = c.id
            JOIN {groups} g ON g.courseid = c.id
            JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid= g.id
            WHERE q.id = ?
            AND u.id NOT IN (
            SELECT ra.userid
            FROM {role_assignments} ra
            JOIN {context} ctx ON ctx.id = ra.contextid
            JOIN {role} r ON r.id = ra.roleid
            WHERE ra.userid = u.id
                AND ctx.contextlevel = 50
                AND ctx.instanceid = c.id
                AND r.shortname IN (\'manager\',\'editingteacher\', \'teacher\')
            )
            ORDER BY u.id
        ',
                [
                    (int) $courseid,
                    (int) $quizid,

                ]
            );

            return $users;

        }

    }
    // --------------- END User Distrpute -----------.

    // ---------- Update Labs --------------------.
    /**
     * Updates lab devices (suspend, activate, delete, admin assign/remove).
     *
     * @param  int    $deviceid   Device ID.
     * @param  string $action     Action type (sus|act|del|admin).
     * @param  string $dataaction Sub-action for admin devices.
     * @param  int    $lab        Lab ID (used for admin actions).
     * @return array Status and message of operation.
     */
    public static function update_labs($deviceid, $action, $dataaction = '', $lab = 0)
    {

        global $DB;
        $params = self::validate_parameters(
            self::update_labs_parameters(),
            [
                'action' => $action,
                'deviceid' => $deviceid,
                'dataaction' => $dataaction,
                'lab' => $lab
            ]
        );

        // Use system context for global admin actions.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/restrict:manage', $context);

        $deviceid = $params['deviceid'];
        $action = $params['action'];
        $dataaction = $params['dataaction'];
        $lab = $params['lab'];

        // Suspend devices in lab.
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

        // Active suspended devices in lab.
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

        // Delete device from DB.
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

        // Device add or remove from admin table.
        if ($action == 'admin') {

            // Remove admin device.
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

            } else {
                // Add Admin Devices.
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
                    return [
                        'status' => 0,
                        'message' => 'Database write error: ' . $e->getMessage()
                    ];
                }
            }

        }

        return ['status' => 0, 'message' => 'Unknown action'];

    }

    // ---------- END Update Labs --------------------.

    // ---------- Quizes To Display --------------------.
    /**
     * Retrieves all quizzes in a course.
     *
     * @param  int $courseid The course ID.
     * @return array Status and list of quizzes (id, name).
     */
    public static function get_quizes($courseid)
    {

        $params = self::validate_parameters(
            self::get_quizes_parameters(),
            [
                'courseid' => $courseid,
            ]
        );

        // Use system context for global admin actions.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/restrict:manage', $context);

        $courseid = $params['courseid'];

        global $DB;

        $quizesarr = array();

        $quizes = $DB->get_records_sql(
            'SELECT * FROM {quiz}
        WHERE course = ?',
            [$courseid]
        );

        foreach ($quizes as $quiz) {
            $quizesarr[] = [
                'id' => $quiz->id,
                'name' => $quiz->name
            ];
        }

        $quizeslist = array_values($quizes);
        if (!empty($quizeslist)) {
            return ['status' => 1, 'message' => $quizesarr];
        } else {
            $quizeslist[] = [
                'id' => 0,
                'name' => 'No Quizes'
            ];
            return ['status' => 0, 'message' => $quizeslist];
        }

    }


    // ---------- Groups To Display --------------------.
    /**
     * Retrieves groups in a course.
     *
     * @param  int $courseid The course ID.
     * @return array Status and list of groups.
     */
    public static function get_groups($courseid)
    {

        $params = self::validate_parameters(
            self::get_groups_parameters(),
            [
                'courseid' => $courseid,
            ]
        );

        // Use system context for global admin actions.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/restrict:manage', $context);

        $courseid = $params['courseid'];

        global $DB;

        $groupsarr = array();

        $groups = $DB->get_records_sql(
            'SELECT * FROM {groups}
        WHERE courseid = ?',
            [$courseid]
        );

        foreach ($groups as $group) {
            $groupsarr[] = [
                'id' => $group->id,
                'name' => $group->name
            ];
        }

        $groupslist = array_values($groups);
        if (!empty($groupslist)) {
            return ['status' => 1, 'message' => $groupsarr];
        } else {
            $groupslist[] = [
                'id' => 0,
                'name' => 'No Quizes'
            ];
            return ['status' => 0, 'message' => $groupsarr];
        }

    }
    // ---------- END Groups To Display --------------------.

    // ---------- Delete Courses Records --------------------.
    /**
     * Deletes user exam records for all quizzes in a course (for redistribution).
     *
     * @param  int    $courseid The course ID.
     * @param  string $action   Action (only "del" supported).
     * @return array Status and message.
     */
    public static function courses_records($courseid, $action)
    {

        // Delete all course exams records to re-distrpute for technical error,.
        // Or no need this data any more.

        global $DB;

        $params = self::validate_parameters(
            self::courses_records_parameters(),
            [
                'courseid' => $courseid,
                'action' => $action
            ]
        );

        // Use system context for global admin actions.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/restrict:manage', $context);

        $courseid = $params['courseid'];

        $action = $params['action'];

        $record = new stdClass();
        $record->id = $courseid;
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

            $sqlparam = [$courseid];

            $rows = $DB->get_records_sql($sql, $sqlparam);

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

    // ---------- END Delete Courses Records --------------------.



    // ---------------------- Returns -------------------------.

    // ---------------------- Update Labs Returns -------------------------.
    /**
     * Summary of update_labs_returns
     *
     * @return external_single_structure
     */
    public static function update_labs_returns()
    {
        return new external_single_structure(
            [
                'status' => new external_value(PARAM_INT, 'Operation status (1 = success, 0 = failure)'),
                'message' => new external_value(PARAM_TEXT, 'Message describing the result'),

            ]
        );
    }


    // --------------------- Get Users Returns -------------------.
    /**
     * Summary of get_users_returns
     *
     * @return external_single_structure
     */
    public static function get_users_returns()
    {
        return new external_single_structure(
            [
                'status' => new external_value(PARAM_INT, '0 = error, 1 = success'),
                'message' => new external_value(PARAM_TEXT, 'Result message'),
                'results' => new external_multiple_structure(
                    new external_value(PARAM_RAW, 'Details of distribution'),
                    'Optional result details',
                    VALUE_OPTIONAL
                )
            ]
        );
    }

    // --------------------- Get Quizes Returns -------------------.
    /**
     * Summary of get_quizes_returns
     *
     * @return external_single_structure
     */
    public static function get_quizes_returns()
    {
        return new external_single_structure(
            [
                'status' => new external_value(PARAM_INT, '0 = error, 1 = success'),
                'message' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'Quiz ID'),
                            'name' => new external_value(PARAM_TEXT, 'Quiz name'),

                        ]
                    )
                )
            ]
        );
    }

    // --------------------- Get Groups Returns -------------------.
    /**
     * Summary of get_groups_returns
     *
     * @return external_single_structure
     */
    public static function get_groups_returns()
    {
        return new external_single_structure(
            [
                'status' => new external_value(PARAM_INT, '0 = error, 1 = success'),
                'message' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'Quiz ID'),
                            'name' => new external_value(PARAM_TEXT, 'Quiz name'),

                        ]
                    )
                )
            ]
        );
    }


    // ------------ Course Records Rerturns --------------.
    /**
     * Summary of courses_records_returns
     *
     * @return external_single_structure
     */
    public static function courses_records_returns()
    {
        return new external_single_structure(
            [
                'status' => new external_value(PARAM_INT, '0 = error, 1 = success'),
                'message' => new external_value(PARAM_TEXT, 'Message describing the result'),
            ]
        );
    }

    // ------------ STOP PROCESS RETURNS -----------------.
    /**
     * Summary of stop_process_returns
     *
     * @return external_single_structure
     */
    public static function stop_process_returns()
    {
        return new external_single_structure(
            [
                'status' => new external_value(PARAM_INT, '0 = error, 1 = success'),
                'message' => new external_multiple_structure(PARAM_TEXT, 'Descripe data')
            ]
        );
    }



}
