<?php

namespace quizaccess_sebversion_checker;

defined('MOODLE_INTERNAL') || die();

class Utils {
    /**
     * Checks whether the SEB Server quiz access plugin is present.
     *
     * @return bool
     */
    public static function isSebServerInstalled(): bool {
        return \core_plugin_manager::instance()->get_plugin_info('quizaccess_sebserver') !== null;
    }
}
