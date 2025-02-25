<?php

// renomeie para config.php

define('AWS_ACCESS_KEY_ID', 'AAAAAAAAA');
define('AWS_SECRET_ACCESS_KEY', 'AAAAAAAAAAAAAAAAAAA');

define('BITLY_KEY', 'aaaaaaaaaaaaaaaaaaaa');
define('BITLY_GROUP', '111111111111');

define('AWS_ASSOCIATE_TAGS', json_encode([
    'default' => 'yourtag-20', // 'default' is required
    'bsky'    => 'yourothertag-20',
    'threads' => 'yourothertag-20',
    'discord' => 'yourothertag-20',
    'stories' => 'yourothertag-20'
]));

// optional: YOURLS integration
define('YOURLS_API_URL', 'http://yourls.example.com');
define('YOURLS_TOKEN', '1234567890');
