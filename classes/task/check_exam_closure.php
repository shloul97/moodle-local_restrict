<?php
namespace local_restrict\task;

defined('MOODLE_INTERNAL') || die();

class check_exam_closure extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('check_exam_closure_task', 'local_restrict');
    }

    public function execute() {
        global $DB;

        $now = time();

        // Get quizzes that have just closed and haven't been processed yet
        $sql = "SELECT q.id
                  FROM {quiz} q
                  JOIN {course_modules} cm ON cm.instance = q.id
                  JOIN {modules} m ON m.id = cm.module
                 WHERE q.timeclose > 0 AND q.timeclose <= :now
                   AND m.name = 'quiz'";
        $quizzes = $DB->get_records_sql($sql, ['now' => $now]);

        foreach ($quizzes as $quiz) {
            // Do something when the exam closes
            $sql_update = "UPDATE {local_restrict_user_exam} set status_id = 0 where examid = ?";
            $quizzes = $DB->execute($sql_update, [$quiz->id]);

            // Add your custom action here: send notification, log, update DB, etc.
        }
    }
}