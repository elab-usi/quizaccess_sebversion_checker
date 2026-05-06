<?php

namespace quizaccess_sebversion_checker\task;

defined('MOODLE_INTERNAL') || die();

use quizaccess_sebversion_checker\UserSEBVersion;

class sebchecker_sync_users extends \core\task\scheduled_task {

    public function get_name() {
        return 'Sync users for SEB Version Checker.';
    }

    public function execute() {
        global $DB;

        mtrace("Starting SEB Version Checker exam session sync...");

        // Get the dates from settings
        $min_date = get_config('quizaccess_sebversion_checker', 'min_date_check');
        $max_date = get_config('quizaccess_sebversion_checker', 'max_date_check');
        if (!$min_date || !$max_date) {
            mtrace("SEB Version Checker Skipping: dates check are not set.");
            return;
        }
        $min_timestamp = strtotime($min_date);
        $max_timestamp = strtotime($max_date);

        $maxplusoneday = strtotime('+1 days', $max_timestamp);



        // If max is minor than today, Checker is not active!
        if($max_timestamp < time() && $maxplusoneday > time()) {
            UserSEBVersion::resetAllSessions(); // all has_session to 0
            mtrace("SEB Version Checker: reset all sessions done.");
            mtrace("SEB Version Checker: dates are in the past. Checker not active!");
            return;
        } else if ($max_timestamp < time()){
            mtrace("SEB Version Checker: dates are in the past. Checker not active!");
            return;
        }

        // Check if min is minor that today date
        $min_timestamp = ($min_timestamp < time()) ? time() : $min_timestamp;


        // Get all users ID enrolled in courses between the dates.
        $sql = "SELECT DISTINCT ue.userid
            FROM {user_enrolments} ue
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {course} c ON c.id = e.courseid
            JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :contextcourse
            JOIN {role_assignments} ra ON ra.userid = ue.userid AND ra.contextid = ctx.id
            WHERE ue.status = :active
            AND c.startdate >= :mindate
            AND c.startdate <= :maxdate
            AND ra.roleid = :roleid";

        $params = [
            'active' => ENROL_USER_ACTIVE,
            'mindate' => $min_timestamp,
            'maxdate' => $max_timestamp,
            'contextcourse' => CONTEXT_COURSE,
            'roleid' => 5
        ];

        $users = $DB->get_records_sql($sql, $params);
        $activeUserIds = array_keys($users);

        // Set to 0 has_session for the people not in the list
        UserSEBVersion::resetUsersSessionsNotInList($activeUserIds);

        // Set to 1 has_session for the people in the list
        UserSEBVersion::userHasSession($activeUserIds);

        // Add new users
        $newUsersCount = 0;
        foreach ($activeUserIds as $userid) {
            if (!UserSEBVersion::checkIfUserExist($userid)) {
                if(UserSEBVersion::addUser($userid, null, 1, 0)) {
                    mtrace("SEB Version Checker: Added successfully user with id " . $userid);
                    $newUsersCount++;
                }
            }
        }


        mtrace("SEB Version Checker - Sync complete. Processed " . count($users) . " users, added " . $newUsersCount . " users.");
    }
}