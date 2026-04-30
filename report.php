<?php

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/classes/UserSEBVersion.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Headers
$url = new moodle_url('/mod/quiz/accessrule/sebversion_checker/report.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('report_settings_title', 'quizaccess_sebversion_checker'));
$PAGE->set_heading(get_string('report_settings_title', 'quizaccess_sebversion_checker'));
$PAGE->set_pagelayout('admin');

$filter_name    = optional_param('f_name', '', PARAM_TEXT);
$filter_noaccess = optional_param('f_noaccess', 0, PARAM_INT); // Checkbox: mai fatto accesso
$filter_insession = optional_param('f_insession', 0, PARAM_INT); // Checkbox: in sessione

echo $OUTPUT->header();

// Render the Filter Form.
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $url, 'class' => 'form-inline mb-3']);
echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'f_name', 'value' => $filter_name, 'placeholder' => get_string('fullname'), 'class' => 'form-control mr-2']);

// Never accessed
echo html_writer::start_tag('div', ['class' => 'form-check mr-2']);
echo html_writer::checkbox('f_noaccess', 1, $filter_noaccess, get_string('filter_noaccess', 'quizaccess_sebversion_checker'), ['class' => 'form-check-input', 'id' => 'f_noaccess']);
echo html_writer::end_tag('div');

// In exam session only
echo html_writer::start_tag('div', ['class' => 'form-check mr-2']);
echo html_writer::checkbox('f_insession', 1, $filter_insession, get_string('filter_insession', 'quizaccess_sebversion_checker'), ['class' => 'form-check-input', 'id' => 'f_insession']);
echo html_writer::end_tag('div');

echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('filter'), 'class' => 'btn btn-primary']);
echo html_writer::link($url, 'Clear all', ['class' => 'btn btn-link']);
echo html_writer::end_tag('form');


// Export
$exporturl = new moodle_url('/mod/quiz/accessrule/sebversion_checker/export.php', ['format' => 'csv']);
echo html_writer::start_div('mb-3');
echo html_writer::link($exporturl, 'Download as CSV', ['class' => 'btn btn-secondary']);
echo html_writer::end_div();


// Configure Table
$table = new flexible_table('seb_version_report_table');
$table->define_baseurl($url);

$columns = ['fullname', 'email', 'version', 'has_session', 'timemodified'];
$headers = [
//    get_string('userid', 'quizaccess_sebversion_checker'),
    get_string('fullname'),
    get_string('email'),
    get_string('version', 'quizaccess_sebversion_checker'),
    get_string('has_session', 'quizaccess_sebversion_checker'),
    get_string('timemodified', 'quizaccess_sebversion_checker')
];

$table->define_columns($columns);
$table->define_headers($headers);
$table->sortable(true, 'timemodified', SORT_DESC);
$table->set_attribute('class', 'admintable generaltable');
$table->setup();

// SQL with Filters.
$userfieldsapi = \core_user\fields::for_identity($context)->with_userpic();
$userfields = $userfieldsapi->get_sql('u', false, '', '', false);

$sql = "SELECT s.id, s.userid, s.version, s.has_session, s.timemodified, 
               u.firstname, u.lastname, u.email, {$userfields->selects}
          FROM {quizaccess_sebversion} s
          JOIN {user} u ON s.userid = u.id";

$where = [];
$params = [];

if ($filter_name) {
    $where[] = $DB->sql_like($DB->sql_fullname('u.firstname', 'u.lastname'), ':name', false);
    $params['name'] = '%' . $filter_name . '%';
}

if ($filter_noaccess) {
    // Si assume che chi non ha mai fatto accesso abbia version NULL o stringa vuota
    $where[] = "(s.version IS NULL OR s.version = '')";
}

if ($filter_insession) {
    $where[] = "s.has_session = 1";
}

if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

if ($sort = $table->get_sql_sort()) {
    $sql .= " ORDER BY $sort";
}

$records = $DB->get_records_sql($sql, array_merge($params, $userfields->params));

// Populate Data.
foreach ($records as $record) {
    $row = [];
//    $row[] = $record->userid;
    $row[] = fullname($record);
    $row[] = $record->email;
    $row[] = $record->version ?: '-';
    $row[] = $record->has_session ? $OUTPUT->pix_icon('i/checked', get_string('yes')) : 'x';
    $row[] = $record->timemodified > 0 ? userdate($record->timemodified) : get_string('never', 'quizaccess_sebversion_checker');

    $table->add_data($row);
}

$table->finish_output();
echo $OUTPUT->footer();