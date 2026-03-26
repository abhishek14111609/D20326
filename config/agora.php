<?php

return [
    'app_id' => env('AGORA_APP_ID'),
    'app_certificate' => env('AGORA_APP_CERTIFICATE'),
    'token_expiration' => env('AGORA_TOKEN_EXPIRATION', 3600),

    // Chat API (Easemob/Agora)
    'api_base' => env('AGORA_API_BASE', 'https://a41.chat.agora.io'),
    'org_name' => env('AGORA_ORG_NAME'),
    'app_name' => env('AGORA_APP_NAME'),
	'app_key' => env('AGORA_APP_KEY'),
	'channel' => env('AGORA_DEFAULT_CHANNEL'),
    'client_id' => env('AGORA_CLIENT_ID'),
    'client_secret' => env('AGORA_CLIENT_SECRET'),
];
