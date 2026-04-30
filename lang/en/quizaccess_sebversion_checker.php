<?php
/**
 * Settings
 */
$string['pluginname'] = 'SEB Version Checker';
$string['enable_windows'] = 'Enable Windows Check';
$string['enable_windows_desc'] = 'If enabled, the plugin will check SEB version on Windows systems.';
$string['allowed_seb_win'] = 'Allowed SEB Windows Versions';
$string['allowed_seb_win_desc'] = 'Enter allowed patterns (one per line). Use * as wildcard.';
$string['enable_mac'] = 'Enable MacOS Check';
$string['enable_mac_desc'] = 'If enabled, the plugin will check SEB version on MacOS systems.';
$string['allowed_seb_mac'] = 'Allowed SEB MacOS Versions';
$string['allowed_seb_mac_desc'] = 'Enter allowed patterns (one per line). Use * as wildcard.';
$string['enable_email_alerts'] = 'Enable Email Notifications';
$string['enable_email_alerts_desc'] = 'Send an email alert when an unauthorized SEB version is detected or if the check has not yet been done.';
$string['enable_prevent_access'] = 'Enable Prevent Access';
$string['enable_prevent_access_desc'] = 'If enabled, blocks access to the quiz if a version is not in the allowed list.';
$string['min_date_check'] = 'Start Exam Session';
$string['min_date_check_desc'] = 'Check version only starting from this date (Format: YYYY-MM-DD). If the user checked before that date, restart sending the email notification.';
$string['max_date_check'] = 'End Exam Session';
$string['max_date_check_desc'] = 'Check version only to that date (Format: YYYY-MM-DD).';

/**
 * Popup strings
 */
$string['checking_seb_version'] = 'Checking SEB version...';
$string['not_yet_inseb'] = 'Safe Exam Browser not yet launched';
$string['installed_version'] = 'Installed version:';
$string['prevent_access_error'] = 'You are not authorized to start this quiz. Please update Safe Exam Browser to the latest version or contact the technical support.';
$string['correct_version'] = 'You have the correct version.';
$string['wrong_version'] = 'You do NOT have the correct version. Please update Safe Exam Browser to the latest version or contact the technical support.';
$string['seb_detected'] = 'Safe Exam Browser detected';

/**
 * Report
 */
$string['report_settings_title'] = 'SEB Version Checker Report';
$string['userid'] = 'User ID';
$string['version'] = 'SEB Version';
$string['has_session'] = 'In Exam Session';
$string['timemodified'] = 'Last Update';
$string['never'] = 'Never';
$string['filter_noaccess'] = 'Never accessed';
$string['filter_insession'] = 'In exam session only';