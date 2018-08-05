<?php

/**
 * This file is part of the dborsatto/object-xml package.
 *
 * @license   MIT
 */

return [
    'name' => 'note',
    'value' => '',
    'attributes' => [
        'to' => 'John',
        'from' => 'Jane',
    ],
    'children' => [
        0 => [
            'name' => 'subject',
            'value' => 'Reminder',
            'attributes' => [],
            'children' => [],
        ],
        1 => [
            'name' => 'body',
            'value' => 'Don\'t forget me this weekend!',
            'attributes' => [],
            'children' => [],
        ],
    ],
];
