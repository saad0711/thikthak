<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Service areas
    |--------------------------------------------------------------------------
    |
    | Dhaka areas people can pick, with the approximate latitude/longitude of
    | each area's centre. Service requests store these coordinates so later
    | features (F2 discovery, F18 matching) can measure distances. A map pin
    | picker can replace this list later without changing the database.
    |
    */

    'areas' => [
        'Badda' => [23.7806, 90.4258],
        'Banani' => [23.7937, 90.4066],
        'Bashundhara' => [23.8193, 90.4526],
        'Dhanmondi' => [23.7461, 90.3742],
        'Farmgate' => [23.7570, 90.3897],
        'Gulshan' => [23.7925, 90.4078],
        'Jatrabari' => [23.7104, 90.4348],
        'Khilgaon' => [23.7520, 90.4250],
        'Lalbagh' => [23.7190, 90.3880],
        'Mirpur' => [23.8223, 90.3654],
        'Mohakhali' => [23.7780, 90.4050],
        'Mohammadpur' => [23.7662, 90.3589],
        'Motijheel' => [23.7330, 90.4172],
        'Rampura' => [23.7612, 90.4207],
        'Tejgaon' => [23.7639, 90.3925],
        'Uttara' => [23.8759, 90.3795],
    ],

    /*
    |--------------------------------------------------------------------------
    | Service request rules (F4)
    |--------------------------------------------------------------------------
    */

    'requests' => [
        'max_photos' => 5,
        'max_photo_kb' => 2048,
        'max_deadline_days' => 60,
    ],

];
