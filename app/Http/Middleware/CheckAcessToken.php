<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use App\Facades\WorkerTokensFacade;

class CheckAcessToken
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
        $accessToken = WorkerTokensFacade::parseBearerToToken($request->header('Authorization'));
        $user = WorkerTokensFacade::getUserByToken($accessToken);
        if ($user === NULL) {
            return response()->json((object)['status' => 'Неверный токен'], 401);
        } else {
            $explodeToken = WorkerTokensFacade::explodeToken($accessToken);
            if (time() - $explodeToken->time > 300) {
                return response()->json((object)['status' => 'Время жизни токена вышло'], 402);
            }
            if ($explodeToken->ip !== $request->getClientIp()) {
                return response()->json((object)['status' => 'некорректный токен!'], 401);
            }
            if ($explodeToken->agent !== WorkerTokensFacade::prepareUserAgent($request->userAgent())) {
                return response()->json((object)['status' => 'некорректный токен!'], 401);
            }
        }
        WorkerTokensFacade::putUserFromCacheByToken($accessToken, $user);
        return $next($request, $user);
    }
}
