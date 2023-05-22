<?php

declare(strict_types=1);

return [
    // 32 byte cryptographically random key used for at-rest encryption of fields in the database (not send to
    // other systems)
    'database_at_rest_key' => base64_decode(env('DATABASE_AT_REST_KEY', '')),

    'sealbox' => [
        'private_key' => env('CMS_SEAL_PRIVKEY', ''),
        'recipient_pub_key' => env('CMS_SEAL_RECIPIENT_PUBKEY', ''),
    ],
];
