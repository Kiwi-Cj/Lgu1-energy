<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ai_recommendations' => [
        'enabled' => env('AI_RECOMMENDATIONS_ENABLED', false),
        'provider' => env('AI_RECOMMENDATIONS_PROVIDER', 'rules'),
        'openai' => [
            'key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'timeout' => env('OPENAI_TIMEOUT', 10),
            'temperature' => env('OPENAI_TEMPERATURE', 0.2),
            'max_tokens' => env('OPENAI_MAX_TOKENS', 180),
        ],
    ],

    'main_meter_sensor' => [
        'token' => env('MAIN_METER_SENSOR_TOKEN'),
    ],

    'submeter_sensor' => [
        'token' => env('SUBMETER_SENSOR_TOKEN', env('MAIN_METER_SENSOR_TOKEN')),
    ],

    'integration_api' => [
        'token' => env('INTEGRATION_API_TOKEN'),
    ],

    // CIMM <-> Energy maintenance sync (Facilities Needing Maintenance page).
    // Separate from integration_api.token above on purpose: that token gates
    // read access to ALL integration endpoints (facilities, meters, energy
    // records, incidents...), which some other consumer may already depend
    // on with a real secret configured. Sharing it here would silently widen
    // what that key grants. Keeping CIMM's own key means it can be set (or
    // rotated) independently.
    'cimm_maintenance_sync' => [
        'token' => env('CIMM_MAINTENANCE_SYNC_TOKEN', 'CIMM_ENERGY_SHARED_KEY_2026'),
    ],

    'mqtt' => [
        'host' => env('MQTT_HOST', '127.0.0.1'),
        'port' => env('MQTT_PORT', 1883),
        'username' => env('MQTT_USERNAME'),
        'password' => env('MQTT_PASSWORD'),
        'client_id' => env('MQTT_CLIENT_ID', 'lgu-energy-laravel-subscriber'),
        'topic' => env('MQTT_SUBMETER_TOPIC', 'lgu/submeters/+/telemetry'),
        'qos' => env('MQTT_QOS', 0),
        'keep_alive' => env('MQTT_KEEP_ALIVE', 10),
        'connect_timeout' => env('MQTT_CONNECT_TIMEOUT', 60),
        'socket_timeout' => env('MQTT_SOCKET_TIMEOUT', 5),
    ],

];
