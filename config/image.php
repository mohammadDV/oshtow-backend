<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" to process images
    | internally. You may choose one of them according to your PHP
    | configuration. By default PHP's "GD Library" implementation is used.
    |
    | Supported: "gd", "imagick"
    |
    */

    'driver' => 'gd',

    //index size
    'index-image-sizes' => [
        'large' => [
            'width' => 800,
            'height' => 450
        ],
        'medium' => [
            'width' => 400,
            'height' => 300
        ],
        'small' => [
            'width' => 80,
            'height' => 60
        ],

    ],

    'default-current-index-image' => 'medium',

    'default-profile-image' => 'https://cdn.varzeshpod.com/static/profile.png',
    'default-background-image' => 'https://cdn.varzeshpod.com/static/background.png',
];
