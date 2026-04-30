<?php

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => '\quizaccess_sebversion_checker\task\sebchecker_sync_users',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '2',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ]
];