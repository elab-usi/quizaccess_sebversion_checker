<?php

use quizaccess_sebversion_checker\Checker;
use quizaccess_sebversion_checker\UserSEBVersion;

defined('MOODLE_INTERNAL') || die();

class quizaccess_sebversion_checker extends mod_quiz\local\access_rule_base {

    private $checker = null;

    public static function make($quizobj, $timenow, $canignorerules) {
        // For each quiz:
        return new self($quizobj, $timenow, $canignorerules);
    }

    public function description() {
        global $USER;
        $output = '';

        // Inizialize Checker and get data from headers
        $this->checker = new Checker();

        // Check if the user is using SEB & if the checker is enabled for this OS
        if ($this->checker->isSeb() && $this->checker->isEnabled()) {

            // Save in DB
            UserSEBVersion::saveUser($USER->id, $this->checker->getCurrentVersion(), time());

            // Return popup with version details
            return [$this->checker->getPopup()];

        } else if(!$this->checker->isSeb() && $this->quiz->seb_requiresafeexambrowser > 0) {
            // Seb required but not started yet
            $output = html_writer::start_div('alert alert-warning');
            $output .= get_string('not_yet_inseb', 'quizaccess_sebversion_checker');
            $output .= html_writer::end_div();
        }

        return [$output];
    }

    public function prevent_access() {
        if ($this->checker === null) {
            $this->checker = new Checker();
        }

        // Lock quiz access if the current version is not correct
        $prevent_access_enabled = get_config('quizaccess_sebversion_checker', 'enable_prevent_access');
        if($prevent_access_enabled && $this->checker->isSeb() && $this->checker->isEnabled() && !$this->checker->isCorrectVersion())
            return $this->checker->getErrorLockPopup();

        return false;
    }
}