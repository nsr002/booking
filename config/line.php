<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LINE Channel Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for LINE Messaging API and LIFF
    |
    */

    'channel_id' => env('LINE_CHANNEL_ID', ''),
    'channel_secret' => env('LINE_CHANNEL_SECRET', ''),
    'channel_access_token' => env('LINE_CHANNEL_ACCESS_TOKEN', ''),
    'liff_id' => env('LINE_LIFF_ID', ''),
];
