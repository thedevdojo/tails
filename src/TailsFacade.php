<?php

namespace Devdojo\Tails;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Devdojo\Tails\Skeleton\SkeletonClass
 */
class TailsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'tails';
    }
}
