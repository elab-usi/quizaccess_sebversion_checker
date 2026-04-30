<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * SEB Version Checker
 *
 * @package    quizaccess_sebversion_checker
 * @copyright  2026 Yann Cuttaz, eLab
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

global $ADMIN;

if ($hassiteconfig) {

    if ($ADMIN->fulltree) {


        $settings->add(new admin_setting_configcheckbox(
            'quizaccess_sebversion_checker/enable_windows',
            get_string('enable_windows', 'quizaccess_sebversion_checker'),
            get_string('enable_windows_desc', 'quizaccess_sebversion_checker'),
            1
        ));

        $settings->add(new admin_setting_configtextarea(
            'quizaccess_sebversion_checker/allowed_seb_win',
            get_string('allowed_seb_win', 'quizaccess_sebversion_checker'),
            get_string('allowed_seb_win_desc', 'quizaccess_sebversion_checker'),
            "3.10\n3.10.*\n3.10.*.*\n3.11\n3.11.*\n3.11.*.*",
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configcheckbox(
            'quizaccess_sebversion_checker/enable_mac',
            get_string('enable_mac', 'quizaccess_sebversion_checker'),
            get_string('enable_mac_desc', 'quizaccess_sebversion_checker'),
            1
        ));

        $settings->add(new admin_setting_configtextarea(
            'quizaccess_sebversion_checker/allowed_seb_mac',
            get_string('allowed_seb_mac', 'quizaccess_sebversion_checker'),
            get_string('allowed_seb_mac_desc', 'quizaccess_sebversion_checker'),
            "3.6\n3.6.*\n3.6.*.*\n3.7\n3.7.*\n3.7.*.*",
            PARAM_TEXT
        ));

//        $settings->add(new admin_setting_configcheckbox(
//            'quizaccess_sebversion_checker/enable_email_alerts',
//            get_string('enable_email_alerts', 'quizaccess_sebversion_checker'),
//            get_string('enable_email_alerts_desc', 'quizaccess_sebversion_checker'),
//            0
//        ));

        $settings->add(new admin_setting_configtext(
            'quizaccess_sebversion_checker/min_date_check',
            get_string('min_date_check', 'quizaccess_sebversion_checker'),
            get_string('min_date_check_desc', 'quizaccess_sebversion_checker'),
            date('Y-m-d'),
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configtext(
            'quizaccess_sebversion_checker/max_date_check',
            get_string('max_date_check', 'quizaccess_sebversion_checker'),
            get_string('max_date_check_desc', 'quizaccess_sebversion_checker'),
            date('Y-m-d'),
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configcheckbox(
            'quizaccess_sebversion_checker/enable_prevent_access',
            get_string('enable_prevent_access', 'quizaccess_sebversion_checker'),
            get_string('enable_prevent_access_desc', 'quizaccess_sebversion_checker'),
            0
        ));

    }

    // Add link to Checker report
    $ADMIN->add('modsettingsquizcat',
        new admin_externalpage(
            'quizaccess_sebversion_checker/report',
            get_string('report_settings_title', 'quizaccess_sebversion_checker'),
            new moodle_url('/mod/quiz/accessrule/sebversion_checker/report.php')
        )
    );
}
