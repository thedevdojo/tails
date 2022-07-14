<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'api_key' => env('TAILS_API_KEY', null),
    'api_endpoint' => 'https://devdojo.com/api/v1/tails',
    'api_endpoint_clear' => 'https://devdojo.com/api/v1/tails-clear',
    'webhook_key' => env('TAILS_WEBHOOK_KEY', null)
];