<?php

// Create task to set user exam status = 0 which is mean exam end
$tasks = [
    [
        'classname' => 'local_restrict\task\check_exam_closure',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];