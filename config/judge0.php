<?php

return [
    'api_key'  => env('JUDGE0_API_KEY', ''),
    'base_url' => env('JUDGE0_BASE_URL', 'https://judge0-ce.p.rapidapi.com'),

    /*
    |--------------------------------------------------------------------------
    | Judge0 Language IDs
    |--------------------------------------------------------------------------
    | Judge0 CE community edition language IDs.
    | Full list: https://ce.judge0.com/languages
    */
    'languages' => [
        'python'     => ['id' => 71,  'label' => 'Python 3',       'ace_mode' => 'python'],
        'javascript' => ['id' => 63,  'label' => 'JavaScript',     'ace_mode' => 'javascript'],
        'java'       => ['id' => 62,  'label' => 'Java',           'ace_mode' => 'java'],
        'cpp'        => ['id' => 54,  'label' => 'C++',            'ace_mode' => 'c_cpp'],
        'php'        => ['id' => 68,  'label' => 'PHP',            'ace_mode' => 'php'],
        'go'         => ['id' => 60,  'label' => 'Go',             'ace_mode' => 'golang'],
        'ruby'       => ['id' => 72,  'label' => 'Ruby',           'ace_mode' => 'ruby'],
    ],

    /*
    | Judge0 status IDs
    | 3 = Accepted, 4 = Wrong Answer, 5 = TLE, 6 = Compilation Error
    */
    'status' => [
        'in_queue'          => 1,
        'processing'        => 2,
        'accepted'          => 3,
        'wrong_answer'      => 4,
        'time_limit'        => 5,
        'compile_error'     => 6,
        'runtime_error_sigsegv' => 7,
        'runtime_error_sigxfsz' => 8,
        'runtime_error_sigfpe'  => 9,
        'runtime_error_sigabrt' => 10,
        'runtime_error_nzec'    => 11,
        'runtime_error_other'   => 12,
        'internal_error'    => 13,
        'exec_format_error' => 14,
    ],
];
