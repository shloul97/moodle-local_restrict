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



namespace local_restrict\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\userlist;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy API provider for local_restrict.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {



    /**
     * Returns metadata about this plugin's data.
     *
     * @param  collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_restrict_user_exam',
            [
                'userid' => 'privacy:metadata:local_restrict_user_exam:userid',
                'examid' => 'privacy:metadata:local_restrict_user_exam:examid',
                'groupid' => 'privacy:metadata:local_restrict_user_exam:groupid',
                'privateip' => 'privacy:metadata:local_restrict_user_exam:privateip',
                'publicip' => 'privacy:metadata:local_restrict_user_exam:publicip',
                'status_id' => 'privacy:metadata:local_restrict_user_exam:statusid',
            ],
            'privacy:metadata:local_restrict_user_exam'
        );
        return $collection;
    }

    /**
     * Get the list of contexts that contain user information.
     *
     * @param  int $userid The user to search.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {local_restrict_user_exam} ue ON ue.userid = :userid
                 WHERE ctx.contextlevel = :systemlevel";

        $params = [
            'userid' => $userid,
            'systemlevel' => CONTEXT_SYSTEM,
        ];

        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Export user data for the given contexts.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_SYSTEM) {
                $records = $DB->get_records('local_restrict_user_exam', ['userid' => $userid]);
                if ($records) {
                    $data = [];
                    foreach ($records as $record) {
                        $data[] = (object) [
                            'examid' => $record->examid,
                            'groupid' => $record->groupid,
                            'privateip' => $record->privateip,
                            'publicip' => $record->publicip,
                            'status_id' => $record->status_id,
                        ];
                    }
                    writer::with_context($context)->export_data(
                        ['Secure Exam Access'],
                        (object) ['exams' => $data]
                    );
                }
            }
        }
    }

    /**
     * Delete data for all users in a context.
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $DB->delete_records('local_restrict_user_exam');
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param userlist $userlist The approved user list.
     */
    public static function delete_data_for_users(\core_privacy\local\request\approved_userlist $userlist) {
        global $DB;

        if ($userlist->get_context()->contextlevel == CONTEXT_SYSTEM) {
            $DB->delete_records_list(
                'local_restrict_user_exam',
                'userid',
                $userlist->get_userids()
            );
        }
    }

    /**
     * Delete data for a single user.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;

        if (!empty($userid)) {
            $DB->delete_records('local_restrict_user_exam', ['userid' => $userid]);
        }
    }

     /**
      * Get users who have data in a given context.
      *
      * @param userlist $userlist
      */
    public static function get_users_in_context(userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $sql = "SELECT userid FROM {local_restrict_user_exam}";
            $userlist->add_from_sql('userid', $sql, []);
        }
    }
}
