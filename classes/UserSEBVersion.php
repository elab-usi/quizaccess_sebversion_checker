<?php


namespace quizaccess_sebversion_checker;

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class UserSEBVersion
 * Manages database persistence for SEB version tracking.
 * * @package    quizaccess_sebversion_checker
 */
class UserSEBVersion
{
    private static string $tablename = 'quizaccess_sebversion';

    /**
     * Creates a new record for a user.
     * * @param int $userid Moodle user ID.
     * @param string|null $version The detected SEB version string.
     * @param int $hassession Whether the user is in an Exam session (1 or 0).
     * @return int The ID of the new record.
     */
    public static function addUser(int $userid, string $version = null, int $hassession, int $time = 0){
        global $DB;

        $record = new stdClass();
        $record->userid = $userid;
        $record->version = $version;
        $record->has_session = $hassession;
        $record->timemodified = $time;

        // Insert
        return $DB->insert_record(self::$tablename, $record);
    }

    /**
     * Checks if a record already exists for the given user.
     * * @param int $userid Moodle user ID.
     * @return bool True if record exists, false otherwise.
     */
    public static function checkIfUserExist(int $userid): bool {
        global $DB;
        return $DB->record_exists(self::$tablename, ['userid' => $userid]);
    }

    /**
     * Retrieves the SEB version record for a specific user.
     * * @param int $userid Moodle user ID.
     * @return stdClass|false The record object or false if not found.
     */
    public static function getUserSEBVersion(int $userid): bool|stdClass
    {
        global $DB;
        return $DB->get_record(self::$tablename, ['userid' => $userid]);
    }

    /**
     * Updates an existing user record with new SEB data.
     * * @param int $userid Moodle user ID.
     * @param string $version New SEB version detected.
     * @param int|null $hassession SEB session status.
     * @return bool True on success, false on failure.
     */
    public static function updateUser(int $userid, string $version = null, int $hassession = null, bool $changetime = true): bool {
        global $DB;

        // Retrieve existing record to obtain the primary 'id' key
        $record = self::getUserSEBVersion($userid);

        if ($record) {
            if($version != null)
                $record->version = $version;
            $record->has_session = $hassession;
            if($changetime)
                $record->timemodified = time();

            return $DB->update_record(self::$tablename, $record);
        }

        return false;
    }

    /**
     * High-level method to save user data.
     * Automatically decides between Insert or Update (Upsert logic).
     * * @param int $userid Moodle user ID.
     * @param string|null $version detected version.
     * @param int|null $hassession session flag.
     * @param int $time Time modified. If the user is a new user without checking, default 0 is ok.
     * @return bool|int Success status or new record ID.
     */
    public static function saveUser(int $userid, ?string $version = null, int $time = 0, ?int $hassession = null): bool {

        $hassession = ($hassession == null) ? self::isUserInExamSession($userid) : $hassession;

        if (self::checkIfUserExist($userid)) {
            return self::updateUser($userid, $version, $hassession);
        } else {
            return self::addUser($userid, $version, $hassession, $time);
        }
    }

    /**
     * Checks if the user is currently in an exam session.
     * A user is considered in a session if they are enrolled in at least one course
     * that started after the "minimum date check" defined in the plugin settings.
     *
     * @param int $userid Moodle user ID.
     * @return bool True if the user has an active exam session, false otherwise.
     */
    public static function isUserInExamSession(int $userid): bool {
        global $DB;

        $min_date_setting = get_config('quizaccess_sebversion_checker', 'min_date_check');
        $max_date_setting = get_config('quizaccess_sebversion_checker', 'max_date_check');
        if (!$min_date_setting) { // just to avoid some errors
            return false;
        }
        $min_timestamp = strtotime($min_date_setting);
        $max_timestamp = strtotime($max_date_setting);

        // If max is minor that today, the user is not in a session
        if($max_timestamp < time()) return false;

        // Check if min is minor that today date
        $min_timestamp = ($min_timestamp < time()) ? time() : $min_timestamp;

        // Check enrollments.
        // We join 'user_enrolments', 'enrol', and 'course' tables.
        // We check if the user is enrolled (ue.status = 0) and the course startdate is >= our setting.
        $sql = "SELECT COUNT(c.id)
                FROM {course} c
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                WHERE ue.userid = :userid
                  AND ue.status = :enrolstatus
                  AND c.startdate >= :mindate
                  AND c.startdate <= :maxdate";

        $params = [
            'userid'      => $userid,
            'enrolstatus' => ENROL_USER_ACTIVE, // Standard Moodle constant for active enrollment (usually 0).
            'mindate'     => $min_timestamp,
            'maxdate'     => $max_timestamp,
        ];

        $count = $DB->count_records_sql($sql, $params);

        return $count > 0;
    }

    /**
     * Resets the session status for all users in the table.
     * Sets 'has_session' to 0 for every record.
     * * @return bool True on success, false on failure.
     */
    public static function resetAllSessions(): bool {
        global $DB;
        return $DB->set_field(self::$tablename, 'has_session', 0, []);
    }

    /**
     * Sets has_session to 1 for all users provided in the list that already exist in the table.
     *
     * @param array $activeUserIds Array of user IDs to activate.
     * @return bool True on success, false otherwise.
     */
    public static function userHasSession(array $activeUserIds): bool {
        global $DB;

        if (empty($activeUserIds)) {
            return true;
        }

        list($insql, $params) = $DB->get_in_or_equal($activeUserIds, SQL_PARAMS_NAMED, 'userid');

        $sql = "UPDATE {" . self::$tablename . "} 
                SET has_session = 1
                WHERE userid $insql";

        return $DB->execute($sql, $params);
    }

    /**
     * Resets session status to 0 for all users NOT included in the provided list.
     * * This is useful during cron synchronization to disable sessions for users
     * who are no longer eligible.
     *
     * @param array $activeUserIds Array of user IDs to keep active.
     * @return bool True on success, false otherwise.
     */
    public static function resetUsersSessionsNotInList(array $activeUserIds): bool {
        global $DB;

        // If the list is empty, we reset everyone since no one is in a session.
        if (empty($activeUserIds)) {
            return self::resetAllSessions();
        }

        // Use the $DB->get_in_or_equal helper to safely handle the array for the SQL query.
        // We set $equal to false to get the "NOT IN" behavior.
        list($insql, $params) = $DB->get_in_or_equal($activeUserIds, SQL_PARAMS_NAMED, 'userid', false);

        // Define the field to update.
        $sql = "UPDATE {" . self::$tablename . "} 
                SET has_session = 0 
                WHERE userid $insql";

        return $DB->execute($sql, $params);
    }

    /**
     * Retrieves all records from the SEB version table joined with user details.
     * * This method joins the custom SEB table with the core Moodle user table
     * to provide a complete dataset including names and email addresses.
     *
     * @return array List of objects containing SEB data and user identification.
     */
    public static function getAllDataForExport(): array {
        global $DB;

        $sql = "SELECT s.id, 
                       s.userid, 
                       u.firstname, 
                       u.lastname, 
                       u.email, 
                       s.version, 
                       s.has_session, 
                       s.timemodified
                FROM {quizaccess_sebversion} s
                JOIN {user} u ON s.userid = u.id
                ORDER BY s.timemodified ASC";

        return $DB->get_records_sql($sql);
    }
}