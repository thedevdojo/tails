<?php

namespace Devdojo\Tails\Commands;

use Illuminate\Console\Command;
use Facades\Devdojo\Tails\Tails;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class Clear extends Command
{
    /**
     * The tails:clear signature for the console command.
     *
     * @var string
     */
    protected $signature = 'tails:clear';

    /**
     * The tails:clear description.
     *
     * @var string
     */
    protected $description = 'Clears the cached results retrived from tails';

    /**
     * Execute the console command to clear the cached pages
     *
     * @return int
     */
    public function handle()
    {
        $tailsCachedPages = Tails::getCacheArray();
        foreach($tailsCachedPages as $cachedPage){
            $cacheKey = 'tails.' . str_replace('/', '.', $cachedPage);
            Cache::forget($cacheKey);
            $this->info("Cleared cached result with key: {$cacheKey}");
        }
        Artisan::call('view:clear');
        Tails::clearOPCache();

    }
}
