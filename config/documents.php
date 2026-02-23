<?php

return [
    'issuer_name' => env('DOC_ISSUER_NAME', config('app.name', 'StockPulse')),
    'issuer_address' => env('DOC_ISSUER_ADDRESS', 'Maputo, Moçambique'),
    'issuer_email' => env('DOC_ISSUER_EMAIL', ''),
    'issuer_contact' => env('DOC_ISSUER_CONTACT', ''),
    'footer_text' => env('DOC_FOOTER_TEXT', 'Powered by Cheesemania'),
];
