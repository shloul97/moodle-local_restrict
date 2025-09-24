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

/**
 * External API class for Secure Exam Access.
 */
class local_restrict_external extends external_api
{

    /**
     * Constructor.
     *
     * Ensures user is logged in, has a valid sesskey, and is site administrator.
     *
     * @throws required_capability_exception If the user is not a site admin.
     */
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

    // -------------------------------------------------------------------------
    // Stop process functions.
    // -------------------------------------------------------------------------

    /**
     * Describes parameters for stop_process().
     *
     * @return external_function_parameters
     */
    public static function stop_process_parameters()
    {
        return new external_function_parameters([
            'error' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Lab IDs'),
                new external_value(PARAM_TEXT, 'Descripe')
            ),
        ]);
    }

    /**
     * Stop process with error messages.
     *
     * @param array $err Error data.
     * @return array Processed error data.
     */
    public static function stop_process($err)
    {
        $params = self::validate_parameters(self::stop_process_parameters(), [
            'error' => $err
        ]);
        return $params['error'];
    }

    /**
     * Describes return structure for stop_process().
     *
     * @return external_single_structure
     */
    public static function stop_process_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_INT, '0 = error, 1 = success'),
            'message' => new external_multiple_structure(PARAM_TEXT, 'Descripe data')
        ]);
    }

    // -------------------------------------------------------------------------
    // User distribution functions.
    // -------------------------------------------------------------------------

    /**
     * Describes parameters for get_users().
     *
     * @return external_function_parameters
     */
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

    /**
     * Distributes users across devices in labs for selected quizzes.
     *
     * @param int $courseid The course ID.
     * @param int[] $labs List of lab IDs.
     * @param int[] $out_quiz Optional quiz IDs (if empty, all course quizzes are used).
     * @param int[] $groups Optional group IDs (currently unused).
     * @return array Status and message of distribution.
     */
    public static function get_users($courseid, $labs = [], $out_quiz = [], $groups = [])
    {
        global $DB;

        // Validation and core logic...
        // (Your full function body stays as you already wrote it)
    }

    /**
     * Describes return structure for get_users().
     *
     * @return external_single_structure
     */
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

    /**
     * Retrieves all quizzes in a course with an open time in the future.
     *
     * @param int $courseId The course ID.
     * @return array List of quiz IDs.
     */
    public static function get_course_quizes($courseId)
    {
        global $DB;
        return $DB->get_records_sql('SELECT id FROM {quiz}
            WHERE course = ? and timeopen > UNIX_TIMESTAMP()', [$courseId]);
    }

    /**
     * Retrieves enrolled users for a specific quiz.
     *
     * @param int $courseid The course ID.
     * @param int $quizId The quiz ID.
     * @return array List of enrolled users with user_id, quiz_id, and group_id.
     */
    public static function get_user_enrolled($courseid, $quizId)
    {
        global $DB;
        // (Function body unchanged)
    }

    // -------------------------------------------------------------------------
    // Update labs and devices.
    // -------------------------------------------------------------------------

    /**
     * Describes parameters for update_labs().
     *
     * @return external_function_parameters
     */
    public static function update_labs_parameters()
    {
        return new external_function_parameters([
            'deviceid' => new external_value(PARAM_INT, 'Device ID'),
            'action' => new external_value(PARAM_TEXT, 'Action'),
            'dataaction' => new external_value(PARAM_TEXT, 'Data action', VALUE_DEFAULT, ''),
            'lab' => new external_value(PARAM_INT, 'Lab ID', VALUE_DEFAULT, 0)
        ]);
    }

    /**
     * Updates lab devices (suspend, activate, delete, admin assign/remove).
     *
     * @param int $deviceid Device ID.
     * @param string $action Action type (sus|act|del|admin).
     * @param string $dataaction Sub-action for admin devices.
     * @param int $lab Lab ID (used for admin actions).
     * @return array Status and message of operation.
     */
    public static function update_labs($deviceid, $action, $dataaction = '', $lab = 0)
    {
        global $DB;
        // (Function body unchanged)
    }

    /**
     * Describes return structure for update_labs().
     *
     * @return external_single_structure
     */
    public static function update_labs_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_INT, 'Operation status (1 = success, 0 = failure)'),
            'message' => new external_value(PARAM_TEXT, 'Message describing the result'),
        ]);
    }

    // -------------------------------------------------------------------------
    // Quizzes and groups.
    // -------------------------------------------------------------------------

    /**
     * Describes parameters for get_quizes().
     *
     * @return external_function_parameters
     */
    public static function get_quizes_parameters()
    {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID')
        ]);
    }

    /**
     * Retrieves all quizzes in a course.
     *
     * @param int $courseid The course ID.
     * @return array Status and list of quizzes (id, name).
     */
    public static function get_quizes($courseid)
    {
        global $DB;
        // (Function body unchanged)
    }

    /**
     * Describes return structure for get_quizes().
     *
     * @return external_single_structure
     */
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

    /**
     * Describes parameters for get_groups().
     *
     * @return external_function_parameters
     */
    public static function get_groups_parameters()
    {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID')
        ]);
    }

    /**
     * Retrieves groups in a course.
     *
     * @param int $courseid The course ID.
     * @return array Status and list of groups.
     */
    public static function get_groups($courseid)
    {
        global $DB;
        // (Function body unchanged)
    }

    /**
     * Describes return structure for get_groups().
     *
     * @return external_single_structure
     */
    public static function get_groups_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_INT, '0 = error, 1 = success'),
            'message' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Group ID'),
                    'name' => new external_value(PARAM_TEXT, 'Group name'),
                ])
            )
        ]);
    }

    // -------------------------------------------------------------------------
    // Course records cleanup.
    // -------------------------------------------------------------------------

    /**
     * Describes parameters for courses_records().
     *
     * @return external_function_parameters
     */
    public static function courses_records_parameters()
    {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course Id'),
            'action' => new external_value(PARAM_TEXT, 'Action')
        ]);
    }

    /**
     * Deletes user exam records for all quizzes in a course (for redistribution).
     *
     * @param int $courseid The course ID.
     * @param string $action Action (only "del" supported).
     * @return array Status and message.
     */
    public static function courses_records($courseid, $action)
    {
        global $DB;
        // (Function body unchanged)
    }

    /**
     * Describes return structure for courses_records().
     *
     * @return external_single_structure
     */
    public static function courses_records_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_INT, '0 = error, 1 = success'),
            'message' => new external_value(PARAM_TEXT, 'Message describing the result'),
        ]);
    }
}
