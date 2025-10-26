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

namespace local_restrict\task;

defined('MOODLE_INTERNAL') || die();
class check_exam_closure extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('check_exam_closure_task', 'local_restrict');
    }

    public function execute() {
        global $DB;
        // Get quizzes that have just closed and haven't been processed yet.
        $sql = "SELECT q.id
                  FROM {quiz} q
                  JOIN {course_modules} cm ON cm.instance = q.id
                  JOIN {modules} m ON m.id = cm.module
                 WHERE FROM_UNIXTIME(q.timeclose) > 0 AND FROM_UNIXTIME(q.timeclose) <= now()
                   AND m.name = 'quiz'";
        $quizzes = $DB->get_records_sql($sql);

        foreach ($quizzes as $quiz) {
            // Do something when the exam closes.
            $record = new stdClass();
            $record->examid = $quiz->id;
            $record->status = 0;
            $DB->update_record('local_restrict_user_exam', $record);
        }
    }
}
