<?php

namespace Devdojo\Tails\Commands;

use Illuminate\Console\Command;
use Facades\Devdojo\Tails\Tails;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Ping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tails:ping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quick check to confirm you have added the correct API key and are talking to the Tails API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $endpoint = config('tails.api_endpoint') . '/tails-ping';
        $apiKey = config('tails.api_key');
        if(is_null($apiKey)){
            abort(400, 'Invalid Tails API Key');
        }

        $response = Http::withToken( $apiKey )->get($endpoint);

        if(!$response->ok()){
            $this->error("Cannot connect the Tails API, please verify you have entered the correct key");
        } else {
            $this->info('pong');
        }

    }
}
