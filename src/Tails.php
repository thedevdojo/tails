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
    public static function getResponse($project){
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
            self::handleErrorResponse($response);
        }
        $jsonResponse = (object)$response->json();
        if(isset($jsonResponse->header)){
            Cache::forever($cacheKey, $jsonResponse);
        }

        return $jsonResponse;
    }

    // function used for the php artisan cache:clear command
    public static function getCacheArray(){
        $endpoint = config('tails.api_endpoint') . '/tails-clear';
        $apiKey = config('tails.api_key');
        if(is_null($apiKey)){
            abort(400, 'Invalid Tails API Key');
        }

        $response = Http::withToken( $apiKey )->get($endpoint);
        if(!$response->ok()){
            self::handleErrorResponse($response);
        }

        $jsonResponse = (object)$response->json();

        return $jsonResponse;
    }

    public static function handleErrorResponse($response){
        $errorType = '';
        if($response->clientError()){
            $errorType = 'Client Error';
        }

        if($response->serverError()){
            $errorType = 'Server Error';
        }

        $body = $response->body();
        abort(400, 'Invalid response from API, please confirm you\'re using the correct API Key and you are calling an existing project.' . $errorType . ' - Body:' . $body);
    }

    // Parse the data from the response
    public static function getDataFromResponse($key, $response){
        if(!isset($response->header)){
            abort(400, 'No response received from the server');
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

        $data = self::replaceBladeHTMLWithBladeDirectives($value);
        return $data;
    }

    // current HTML tags that are replaced and converted into blade making the page dynamic
    public static function replaceBladeHTMLWithBladeDirectives($string){
        $string = str_replace('<ifauth>', '@auth', $string);
        $string = str_replace('</ifauth>', '@endauth', $string);
        $string = str_replace('<ifAuth>', '@auth', $string);
        $string = str_replace('</ifAuth>', '@endauth', $string);

        $string = str_replace('<ifguest>', '@guest', $string);
        $string = str_replace('</ifguest>', '@endguest', $string);
        $string = str_replace('<ifGuest>', '@guest', $string);
        $string = str_replace('</ifGuest>', '@endguest', $string);

        // trim any curly brace from {{ array.item }} to {{ $array->item }}
        $bladeCurlyBraceMatches = [];
        preg_match_all('/{{(.*?)}}/', $string, $bladeCurlyBraceMatches);
        foreach($bladeCurlyBraceMatches[1] as $index => $curlyBrace){
            $trimmedContent = trim($curlyBrace);
            // dd($trimmedContent[0]);
            if(isset($trimmedContent[0])){
                // if it's a string we don't replace it
                if($trimmedContent[0] != "'" && $trimmedContent[0] != '"'){
                    $outputVariable = str_replace('.', '->', $trimmedContent);
                    $string = str_replace($bladeCurlyBraceMatches[0][$index], '{{ $' . $outputVariable . '}}', $string);
                }
            }
        }

        // trim any curly brace from {!! array.item !!} to {!! $array->item !!}
        $bladeCurlyBraceMatches = [];
        preg_match_all('/{!!(.*?)!!}/', $string, $bladeCurlyBraceMatches);
        foreach($bladeCurlyBraceMatches[1] as $index => $curlyBrace){
            $trimmedContent = trim($curlyBrace);
            // dd($trimmedContent[0]);
            if(isset($trimmedContent[0])){
                // if it's a string we don't replace it
                if($trimmedContent[0] != "'" && $trimmedContent[0] != '"'){
                    $outputVariable = str_replace('.', '->', $trimmedContent);
                    $string = str_replace($bladeCurlyBraceMatches[0][$index], '{!! $' . $outputVariable . '!!}', $string);
                }
            }
        }

        foreach(config('tails.blade_tags') as $tag => $value){
            $string = str_replace($tag, $value, $string);
        }

        return $string;
    }

    // Are we getting a specific value from this project like the :html or the :page.styles
    public static function getKeyFromProjectString($projectString){
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
        $project = $project . ':html';
        [$data, $project, $projectPage, $key] = self::getProjectDataFromString($project);
        if(is_null($data)){
            $viewLocation = 'tails::' . $project . '.' . $projectPage;
        } else {
            $viewLocation = self::storeBladeFile( $project, $projectPage, $data );
        }
        Route::view($route, $viewLocation);
    }
    

    public static function storeBladeFile($project, $projectPage, $contents, $key = 'html'){
        $viewFolder = config('tails.directory') . '/' . $project;
        if(empty($projectPage)){
            $projectPage = 'index';
        }
        $partial = '';
        if($key != 'html'){
            $partial .= '/' . str_replace('.', '/', $key);
        }
        $file = $viewFolder . '/' . $projectPage . $partial . '.blade.php';
        if(!file_exists($file)){
            if (!file_exists( dirname($file) )) {
                mkdir(dirname($file), 0777, true);
            }
            file_put_contents($file, $contents);
        }
        
        $viewLocation = 'tails::' . $project . '.' . $projectPage;

        return $viewLocation;
    }

    public static function getProjectDataFromString($projectString){
        $projectStringTrimmed = trim(trim($projectString, "'"), '"');     
        $key = self::getKeyFromProjectString($projectStringTrimmed);
        $projectStringWithoutKey = str_replace(':' . $key, '', $projectStringTrimmed);

        $projectArray = explode('.', $projectStringWithoutKey);
        $project = $projectArray[0];
        $projectPage = '';
        
        if(isset($projectArray[1])){
            $projectPage = $projectArray[1];
        }

        $projectURL = $project;
        if(!empty($projectPage)){
            $projectURL = $project . '/' . $projectPage;
        }

        if($key == ''){
            $key = 'body';
        }

        if(empty($projectPage)){
            $projectPage = 'index';
        }

        if(!view()->exists('tails::' . $project . '.' . $projectPage)){
            abort(400, 'cannot find view for project at tails::' . $project . '.' . $projectPage);
            $response = self::getResponse($projectURL);
            $data = self::getDataFromResponse($key, $response);
        }   else {
            $data = null;
        }

        return [$data, $project, $projectPage, $key];
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

        $tailsViewFolder = config('tails.directory');
        $this->recursiveDeleteTailsViewFolder($tailsViewFolder);

        Artisan::call('view:clear');
        $this->clearOPCache();

        
        \Log::info('Cleared cache for key: ' . $cacheKey);
    }

    public function recursiveDeleteTailsViewFolder($str) {
        if (is_file($str)) {
            return @unlink($str);
        }
        elseif (is_dir($str)) {
            $scan = glob(rtrim($str,'/').'/*');
            foreach($scan as $index=>$path) {
                $this->recursiveDeleteTailsViewFolder($path);
            }
            return @rmdir($str);
        }
    }

    // This function will clear OP Cache if it is enabled
    public function clearOPCache(){
        if( is_array(opcache_get_status()) ? 'enabled' : 'disabled' ){
            opcache_reset();
        }
    }
}
