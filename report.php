<?php

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/classes/UserSEBVersion.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// 1. Estrazione parametri
$filter_name      = optional_param('f_name', '', PARAM_TEXT);
$filter_noaccess  = optional_param('f_noaccess', 0, PARAM_INT);
$filter_insession = optional_param('f_insession', 0, PARAM_INT);

// 2. Costruzione URL con parametri per la tabella
$baseurl = new moodle_url('/mod/quiz/accessrule/sebversion_checker/report.php');
$tableurl = clone($baseurl);

if ($filter_name) {
    $tableurl->param('f_name', $filter_name);
}
if ($filter_noaccess) {
    $tableurl->param('f_noaccess', $filter_noaccess);
}
if ($filter_insession) {
    $tableurl->param('f_insession', $filter_insession);
}

$PAGE->set_url($tableurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('report_settings_title', 'quizaccess_sebversion_checker'));
$PAGE->set_heading(get_string('report_settings_title', 'quizaccess_sebversion_checker'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

// --- 3. LOGICA FILTRI SQL ---
$where = [];
$params = [];

if ($filter_name) {
    $where[] = $DB->sql_like($DB->sql_fullname('u.firstname', 'u.lastname'), ':name', false);
    $params['name'] = '%' . $filter_name . '%';
}

if ($filter_noaccess) {
    $where[] = "(s.version IS NULL OR s.version = '')";
}

if ($filter_insession) {
    $where[] = "s.has_session = 1";
}

// Filters
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $baseurl, 'class' => 'form-inline mb-3']);
echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'f_name', 'value' => $filter_name, 'placeholder' => get_string('fullname'), 'class' => 'form-control mr-2']);

echo html_writer::start_tag('div', ['class' => 'form-check mr-2']);
echo html_writer::checkbox('f_noaccess', 1, $filter_noaccess, "Never accessed", ['class' => 'form-check-input', 'id' => 'f_noaccess']);
echo html_writer::end_tag('div');

echo html_writer::start_tag('div', ['class' => 'form-check mr-2']);
echo html_writer::checkbox('f_insession', 1, $filter_insession, "In session", ['class' => 'form-check-input', 'id' => 'f_insession']);
echo html_writer::end_tag('div');

echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('filter'), 'class' => 'btn btn-primary mr-2']);
echo html_writer::link($baseurl, 'Clear all', ['class' => 'btn btn-link']);
echo html_writer::end_tag('form');

// Export button
$exporturl = new moodle_url('/mod/quiz/accessrule/sebversion_checker/export.php', [
    'f_name' => $filter_name,
    'f_noaccess' => $filter_noaccess,
    'f_insession' => $filter_insession,
    'format' => 'csv'
]);
echo html_writer::start_div('mb-3');
echo html_writer::link($exporturl, 'Download CSV', ['class' => 'btn btn-secondary']);
echo html_writer::end_div();

// Pagination
$recordperpage = 50;
$total_absolute = $DB->count_records('quizaccess_sebversion');
$sql_count = "SELECT COUNT(s.id) FROM {quizaccess_sebversion} s JOIN {user} u ON s.userid = u.id";
if ($where) { $sql_count .= " WHERE " . implode(' AND ', $where); }

$total_filtered = $DB->count_records_sql($sql_count, $params);

$summary_text = get_string('results', 'quizaccess_sebversion_checker') ."<strong>$total_filtered</strong>/<strong>$total_absolute</strong>";

echo html_writer::tag('p', $summary_text, ['class' => 'mt-3 mb-2']);

// Table config
$table = new flexible_table('seb_version_report_table');
$table->define_baseurl($tableurl);

$columns = ['fullname', 'email', 'version', 'has_session', 'timemodified'];
$headers = [get_string('fullname'), get_string('email'), "Version", "In Session", "Last Update"];

$table->define_columns($columns);
$table->define_headers($headers);
$table->sortable(true, 'timemodified', SORT_DESC);
$table->set_attribute('class', 'admintable generaltable');
$table->setup();

$table->pagesize($recordperpage, $total_filtered);


// Query
$userfieldsapi = \core_user\fields::for_identity($context)->with_userpic();
$userfields = $userfieldsapi->get_sql('u', false, '', '', false);

$sql = "SELECT s.id, s.userid, s.version, s.has_session, s.timemodified, {$userfields->selects}
          FROM {quizaccess_sebversion} s
          JOIN {user} u ON s.userid = u.id";

if ($where) { $sql .= " WHERE " . implode(' AND ', $where); }
if ($sort = $table->get_sql_sort()) { $sql .= " ORDER BY $sort"; }

$records = $DB->get_records_sql($sql, array_merge($params, $userfields->params), $table->get_page_start(), $table->get_page_size());

foreach ($records as $record) {
    $row = [];
    $row[] = fullname($record);
    $row[] = $record->email;
    $row[] = $record->version ?: '-';
    $row[] = $record->has_session ? $OUTPUT->pix_icon('i/checked', get_string('yes')) : 'x';
    $row[] = $record->timemodified > 0 ? userdate($record->timemodified) : "Never";
    $table->add_data($row);
}

$table->finish_output();
echo $OUTPUT->footer();