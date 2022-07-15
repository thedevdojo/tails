<?php

namespace Devdojo\Tails\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void get($route, $project)
 * @method static string getDataFromResponse($key, $response)
 * @method static object getCacheArray()
 * @method static string getKeyFromProjectString($projectString)
 * @method static object getResponse($project)
 * 
 * @see \Devdojo\Tails\Tails;
 */
class Tails extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'tails';
    }
}
