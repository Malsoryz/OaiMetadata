<?php

return [
    'verb' => [
        'missing' => 'The request does not provide any verb.',
        'repeated' => 'Do not use the same \'verb\' argument more than once.',
        'illegal' => "':hint' is a illegal verb.",
    ],
    'argument' => [
        'missing' => "Missing ':hint' argument.",
        'repeated' => "Do not use the same ':hint' argument more than once.",
        'illegal' => "':hint' is a illegal argument.",
        'invalid' => [
            'from' => "Invalid 'from' argument value ':hint'",
            'until' => "Invalid 'until' argument value ':hint'",
        ]
    ],
    'set' => [
        'not-supported' => 'Sets Hierarchy is not supported by this repository.',
    ],
    'record' => [
        'no-match' => [
            'set' => "No records match with set ':hint'.",
            'all' => "Query Combination does not produce any records"
        ],
        'id-doesnt-exist' => "Record with identifier ':hint' doesn't exist."
    ],
    'metadata' => [
        'cannot-disseminate' => "Metadata format ':hint' is not supported by this repository."
    ],
    'resumption-token' => 'Invalid or Expired resumption token.',
];