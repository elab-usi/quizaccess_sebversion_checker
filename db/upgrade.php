<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_quizaccess_sebversion_checker_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026100100) {
        $table = new xmldb_table('quizaccess_sebversion');
        $field = new xmldb_field('os', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'version');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Infer the OS for existing records that have not yet stored it.
        $records = $DB->get_recordset_select('quizaccess_sebversion',
            "(os IS NULL OR os = :emptyos) AND version IS NOT NULL AND version <> :emptyversion",
            ['emptyos' => '', 'emptyversion' => ''], '', 'id, version');
        try {
            foreach ($records as $record) {
                $version = trim($record->version);
                if (!preg_match('/^[0-9]+(?:\.[0-9]+)*$/', $version)) {
                    continue;
                }

                $os = version_compare($version, '3.7.1', '<=') ? 'MacOS' : 'Windows';
                $DB->set_field('quizaccess_sebversion', 'os', $os, ['id' => $record->id]);
            }
        } finally {
            $records->close();
        }

        upgrade_plugin_savepoint(true, 2026100100, 'quizaccess', 'sebversion_checker');
    }

    return true;
}
