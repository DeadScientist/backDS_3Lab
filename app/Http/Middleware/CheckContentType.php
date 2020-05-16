<?php

namespace App\Http\Middleware;

use Closure;
use function PHPSTORM_META\override;

class CheckContentType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->getContentType() !== "json") {
            return response()->json((object) [
                "status" => "Не поддерживаемый тип данных!"
            ], 415);
        }
        $responseNext = $next($request);
        $responseNext->header('expires', time());
        return $responseNext;
    }
}
