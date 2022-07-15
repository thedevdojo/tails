<?php

namespace Devdojo\Tails\Commands;

use Devdojo\Tails\Facades\Tails;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class Clear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tails:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears the cached results retrived from tails';

    /**
     * Execute the console command.
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

    }
}
