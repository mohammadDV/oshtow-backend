<?php

return [
    'site' => env('SITE_FEE', '10'), // percent
    'bank_details' => [
        'sheba' => env('BANK_SHEBA', 'IR360560611828006269870901'),
        'bank_name' => env('BANK_NAME', 'بلو بانک'),
        'card_number' => env('BANK_CARD_NUMBER', '6219861826984279'),
        'owner_name' => env('BANK_OWNER_NAME', 'حمیدرضا ذعاب'),
    ]
];
