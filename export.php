<?php

require_once(__DIR__ . '/../../../../config.php');
require_once(__DIR__ . '/classes/UserSEBVersion.php');

use quizaccess_sebversion_checker\UserSEBVersion;

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);
$format = optional_param('format', 'csv', PARAM_ALPHA);
$data = UserSEBVersion::getAllDataForExport();


// CSV Export
if ($format === 'csv') {
    $filename = 'seb_report_' . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Create a file pointer connected to the output stream.
    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM for Excel compatibility.
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // CSV Headers.
    fputcsv($output, [
        'User ID',
        'Firstname',
        'Lastname',
        'Email',
        'SEB Version',
        'In Exam Session',
        'Last Update'
    ]);

    // Data Rows.
    foreach ($data as $row) {
        fputcsv($output, [
            $row->userid,
            $row->firstname,
            $row->lastname,
            $row->email,
            $row->version ?: '-',
            $row->has_session ? 'Yes' : 'No',
            $row->timemodified > 0 ? date('Y-m-d H:i:s', $row->timemodified) : 'Never'
        ]);
    }

    fclose($output);
    exit;
}
