<?php

$webhook_url = config('tails.webhook_url');

// The tails webhook route
Route::post($webhook_url, '\Devdojo\Tails\Tails@webhook');