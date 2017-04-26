<?php
return
[
    [
        '_label' => 'VALID',
        'id' => 1,
        'title' => 'first test record',
        'anons' => 'anons for first test record',
        'channels' => [10,20,30],
        'rating'   => 5,
        'publicDate' => '2017-01-01T00:00:00+03:00',
        'tags' => [
            [
                'id' => 15,
                'title' => 'pop'
            ]
        ]
    ],
    [
        '_label' => 'INVALID',
        'id' => 2,
        'title' => 'second test record',
        'anons' => 'anons for second test record',
        'channels' => [10],
        'rating'   => 1,
        'publicDate' => '2016-01-01T00:00:00+03:00',
    ],
];