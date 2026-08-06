<?php

return [
    'legacy_auth' => env('WHATSAPP_AUTH', ''),
    'legacy_ip' => env('WHATSAPP_IP', ''),
    'legacy_port' => env('WHATSAPP_PORT', 3003),
    'legacy_device_id' => env('WHATSAPP_DEVICE_ID', ''),
    'legacy_action' => env('WHATSAPP_ACTION', 'stop'),
    'legacy_duration' => env('WHATSAPP_DURATION', 86400),
    'base_url' => env('WA_GATEWAY_URL', 'http://127.0.0.1:3000'),
    'username' => env('WA_GATEWAY_USERNAME', ''),
    'password' => env('WA_GATEWAY_PASSWORD', ''),
    'device_id' => env('WA_GATEWAY_DEVICE_ID', ''),
    'admin_wa' => env('WA_ADMIN_NOTIF_NUMBER', ''),
    'webhook_url' => env('WEBHOOK_WA_N8N', ''),
    'timeout' => env('WA_GATEWAY_TIMEOUT', 10),
];
