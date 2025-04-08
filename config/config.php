<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'api_key' => env('TAILS_API_KEY', null),
    'api_endpoint' => 'https://tails.devdojo.com/api/v2',
    'webhook_key' => env('TAILS_WEBHOOK_KEY', null),
    'webhook_url' => env('TAILS_WEBHOOK_URL', 'tails/webhook'),
    
    // You can convert HTML tags to any desired blade tags. This conversion will happen 
    // when the content is pulled from Tails, it will then be rendered as blade tags.
    'blade_tags' => [
        '<userloop>' => '@foreach(\App\Models\User::all() as $user)',
        '</userloop>' => '@endforeach',
    ],

    // This is where the temporary Tails files will be stored
    'directory' => base_path( env('TAILS_DIRECTORY', 'storage/app/tails-tmp') )
];