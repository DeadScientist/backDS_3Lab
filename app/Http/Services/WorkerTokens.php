<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 02.05.20
 * Time: 11:32
 */

namespace App\Http\Services;

use App\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class WorkerTokens
{
    public function parseBearerToToken($token) {
        if(Str::startsWith($token, 'Bearer ')) {
            return(Str::substr($token, 7));
        }
        return $token;
    }

    public function prepareUserAgent($UserAgent) {
        return substr($UserAgent, 0, 60);
    }

    public function getUserByToken($accessToken) {
        $user = null;
        if (Cache::has($accessToken)) {
            $user = Cache::get($accessToken);
        } else {
            $user = User::where('access_token',$accessToken)->first();
        }
        return $user;
    }

    public function putUserFromCacheByToken($accessToken, $user) {
        Cache::put($accessToken, $user, 300);
    }

    public function deleteUserFromCacheByToken($accessToken) {
        if (isset($accessToken)) {
            if (Cache::has($accessToken)) {
                Cache::forget($accessToken);
            }
        }
    }

    public function explodeToken($token) {
        $decodeToken = base64_decode($token);
        $explodeToken = explode("$", $decodeToken);
        return (object) [
            'time' => $explodeToken[0],
            'ip' => $explodeToken[1],
            'agent' => $explodeToken[2],
            'hash' => $explodeToken[3]
        ];
    }
}