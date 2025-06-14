<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VdoCipher API Key
    |--------------------------------------------------------------------------
    |
    | This value is the API key for your VdoCipher account. This key is
    | used to authenticate all API requests to the VdoCipher service.
    |
    */
    'api_key' => env('VDOCIPHER_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | VdoCipher API Base URL
    |--------------------------------------------------------------------------
    |
    | This is the base URL for the VdoCipher API. You shouldn't need to
    | change this value unless VdoCipher changes their API endpoint.
    |
    */
    'base_url' => env('VDOCIPHER_BASE_URL', 'https://dev.vdocipher.com/api'),

    /*
    |--------------------------------------------------------------------------
    | OTP Time-to-Live (TTL)
    |--------------------------------------------------------------------------
    |
    | This value determines how long (in seconds) an OTP token will be valid
    | for video playback. The default is 300 seconds (5 minutes).
    |
    */
    'otp_ttl' => env('VDOCIPHER_OTP_TTL', 300),

    /*
    |--------------------------------------------------------------------------
    | Default Watermarks
    |--------------------------------------------------------------------------
    |
    | Define the default watermarks to apply to videos. These will be used
    | unless overridden when requesting an OTP.
    |
    | Each watermark should be an array with the following keys:
    | - type: The type of watermark (e.g., 'rtext' for running text)
    | - text: The text to display (for text watermarks)
    | - alpha: Opacity (0.0 to 1.0)
    | - color: Color in hex format (e.g., '0xFFFFFF' for white)
    | - size: Font size
    | - interval: Interval between appearances (for running text)
    |
    */
    'watermarks' => [
        // Example watermark configuration:
        // [
        //     'type'     => 'rtext',
        //     'text'     => 'Copyright © ' . date('Y'),
        //     'alpha'    => '0.6',
        //     'color'    => '0xFFFFFF',
        //     'size'     => '15',
        //     'interval' => '5000',
        // ],
    ],
];
