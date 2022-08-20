<?php

namespace Devdojo\Tails\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class BladeViewsDoNotExist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
            $response = $next($request);
            $exception = $response->exception;

            // if it is null or or if it is not of type FileNotFoundException then return the response
            if(is_null($exception) || (!is_null($exception) && (get_class($exception) != \Illuminate\View\ViewException::class) ) ){
                return $response;
            }
            return redirect($request->getRequestUri());
     
    }
}
