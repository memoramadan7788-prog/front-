<?php

/*
 | Hotspot: x / y / w / h are percentages of the 16:9 scene.
 | target:  'scene:<name>'  -> route('frontend.hall.<name>')
 |          'route:<name>'  -> route('frontend.<name>') (falls back to '#' until that route exists)
 */
return [
    'exterior' => [
        'image' => 'images/halls/exterior.webp',
        'alt'   => 'TalentBridge career hall entrance',
        'hotspots' => [
            ['label' => 'Click here to enter', 'target' => 'scene:landing', 'x' => 36, 'y' => 46, 'w' => 28, 'h' => 16, 'transition' => 'enter'],
        ],
    ],

    'lobby' => [
        'image' => 'images/halls/lobby.webp',
        'alt'   => 'TalentBridge career hall lobby',
        'hotspots' => [
            // the big white screen on the left wall
            ['label' => 'Jobs',          'target' => 'route:jobs.index', 'x' => 3,  'y' => 14, 'w' => 18, 'h' => 42],
            // reception desk
            ['label' => 'Help Desk',     'target' => 'route:assistant',  'x' => 36, 'y' => 57, 'w' => 33, 'h' => 20],
            // lounge area behind the desk
            ['label' => 'Lounge',        'target' => 'route:lounge',     'x' => 38, 'y' => 40, 'w' => 26, 'h' => 14],
            // the blue arch on the right
            ['label' => 'Exhibit Halls', 'target' => 'scene:exhibit',    'x' => 83, 'y' => 14, 'w' => 15, 'h' => 52],
        ],
    ],

    'exhibit' => [
        'image' => 'images/halls/exhibit.webp',
        'alt'   => 'TalentBridge exhibit hall',
        'hotspots' => [
            // company booths are added here once companies/booths exist in the DB
            ['label' => 'Back to Lobby', 'target' => 'scene:lobby', 'x' => 40, 'y' => 86, 'w' => 20, 'h' => 9],
        ],
    ],

    // top navigation bar (shown on every hall page when logged in)
    'nav' => [
        ['label' => 'Lobby',         'target' => 'scene:lobby',      'icon' => '🏛️'],
        ['label' => 'Exhibit Halls', 'target' => 'scene:exhibit',    'icon' => '🏢'],
        ['label' => 'Jobs',          'target' => 'route:jobs.index', 'icon' => '💼'],
        ['label' => 'Lounge',        'target' => 'route:lounge',     'icon' => '💬'],
        ['label' => 'My Meetings',   'target' => 'route:candidate.interviews.index', 'icon' => '📅'],
        ['label' => 'Assistant',     'target' => 'route:assistant',  'icon' => '🤖'],
    ],
];