<?php

namespace Devdojo\Tails;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

class Tails
{
    // get the response from a specific project
    public function getResponse($project){
        $cacheKey = 'tails.' . str_replace('/', '.', $project);
        if( Cache::has($cacheKey) ){
            return Cache::get($cacheKey);
        }
        $endpoint = config('tails.api_endpoint') . '/tails' . '/' . $project;
        $apiKey = config('tails.api_key');
        if(is_null($apiKey)){
            abort(400, 'Invalid Tails API Key');
        }
        $response = Http::withToken( $apiKey )->get($endpoint);
        if(!$response->ok()){
            abort(400, 'Invalid response from API, please confirm you\'re using the correct API Key and you are calling an existing project.');
        }
        $jsonResponse = (object)$response->json();
        if(isset($jsonResponse->header)){
            Cache::forever($cacheKey, $jsonResponse);
        }

        return $jsonResponse;
    }

    // function used for the php artisan cache:clear command
    public function getCacheArray(){
        $endpoint = config('tails.api_endpoint') . '/tails-clear';
        $apiKey = config('tails.api_key');
        if(is_null($apiKey)){
            abort(400, 'Invalid Tails API Key');
        }

        $response = Http::withToken( $apiKey )->get($endpoint);
        if(!$response->ok()){
            abort(400, 'Invalid response from API, please confirm you\'re using the correct API Key and you are calling an existing project.');
        }

        $jsonResponse = (object)$response->json();

        return $jsonResponse;
    }

    // Parse the data from the response
    public function getDataFromResponse($key, $response){
        if(!isset($response->header)){
            abort(400, 'No response received from the server');
        }

        if($key == ''){
            $key = 'body';
        }
        
        if(strpos($key, '.') !== false){
            $keys = explode('.', $key);
            if(isset($keys[0]) && isset($keys[1])){
                $value = $response->{$keys[0]}[$keys[1]];
            } else {
                $value = 'Invalid Response Key';
            }
        } else {
            $value = $response->{$key};
        }

        $data = $this->replaceBladeHTMLWithBladeDirectives($value);
        return $data;
    }

    // current HTML tags that are replaced and converted into blade making the page dynamic
    private function replaceBladeHTMLWithBladeDirectives($string){
        $string = str_replace('<ifauth>', '@auth', $string);
        $string = str_replace('</ifauth>', '@endauth', $string);
        $string = str_replace('<ifAuth>', '@auth', $string);
        $string = str_replace('</ifAuth>', '@endauth', $string);

        $string = str_replace('<ifguest>', '@guest', $string);
        $string = str_replace('</ifguest>', '@endguest', $string);
        $string = str_replace('<ifGuest>', '@guest', $string);
        $string = str_replace('</ifGuest>', '@endguest', $string);

        return $string;
    }

    // Are we getting a specific value from this project like the :html or the :page.styles
    public function getKeyFromProjectString($projectString){
        $key = '';
        if(strpos($projectString, ':') !== false){
            $keyArray = explode(':', $projectString);
            if(isset($keyArray[1])){
                $key = $keyArray[1];
            }
        }
        return $key;
    }

    // This is the function that is called for Tails::get()
    public static function get($route, $project){
        Route::view($route, 'tails::page', ['project' => $project]);
    }

    // The function that runs from the incoming webhook
    public function webhook(Request $request){
        $jsonResponse = json_decode($request->getContent());

        $key = $jsonResponse->key;

        if($key != config('tails.webhook_key')){
            \Log::error('Invalid Webhook Key');
            return;
        }

        $project = json_decode($jsonResponse->project);
        $page = json_decode($jsonResponse->page);

        $page_slug = '';
        if($page->slug != '/' && $page->slug != 'home'){
            $page_slug = '.' . $page->slug;
        }

        $cacheKey = 'tails.' . $project->slug . $page_slug;
        Cache::forget($cacheKey);

        Artisan::call('view:clear');
        
        \Log::info('Cleared cache for key: ' . $cacheKey);
    }
}
