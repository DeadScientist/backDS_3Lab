<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 02.05.20
 * Time: 9:18
 */

namespace App\Http\Services;


use App\Facades\WorkerTokensFacade;
use Illuminate\Support\Str;
use App\User;
use Illuminate\Support\Facades\Hash;

class UserServices
{
    private function generatedTokens($userIP, $agent) {
        $stringBaseToken = "" . time() . "$" . $userIP . "$" . $agent . "$";
        $accessToken = base64_encode($stringBaseToken . Str::random(40));
        $refreshToken = base64_encode($stringBaseToken . Str::random(40));
        return (object) [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }

    public function registerUser($firstName, $lastName, $email, $password, $clientIP, $clientAgent) {
        if (isset($firstName) && isset($lastName) && isset($email) && isset($password)  && isset($clientIP)  && isset($clientAgent)) {
            if (User::where('email',$email)->first() === NULL) {
                $tokens = $this->generatedTokens($clientIP, $clientAgent);
                $result = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'access_token' => $tokens->access_token,
                    'refresh_token' => $tokens->refresh_token
                ]);
                WorkerTokensFacade::putUserFromCacheByToken($tokens->access_token, $result);
                return (object) ['result' => $tokens, 'code' => 200];
            } else {
                return (object) ['result' => 'Пользователь с таким Email уже существует!', 'code' => 409];
            }
        } else {
            return (object) ['result' => 'Не все параметры были присланы', 'code' => 500];
        }
    }

    public function loginUser($email, $password, $clientIP, $clientAgent) {
        $user = User::where('email',$email)->first();
        if ($user === NULL) {
            return (object) ['result' => 'Пользователя с таким Email не существует!', 'code' => 404];
        } else {
            if (Hash::check($password, $user->password)) {
                $tokens = $this->generatedTokens($clientIP, $clientAgent);
                WorkerTokensFacade::deleteUserFromCacheByToken($user->access_token);
                $user->access_token = $tokens->access_token;
                $user->refresh_token = $tokens->refresh_token;
                $user->save();
                $result = [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'access_token' => $tokens->access_token,
                    'refresh_token' => $tokens->refresh_token
                ];
                WorkerTokensFacade::putUserFromCacheByToken($tokens->access_token, $user);
                return (object) ['result' => $result, 'code' => 200];
            } else {
                return (object) ['result' => 'Неверный пароль!', 'code' => 401];
            }
        }
    }

    public function refreshTokens($inRefreshToken, $inIp, $inAgent) {
        $user = User::where('refresh_token',$inRefreshToken)->firstOrFail();
        WorkerTokensFacade::deleteUserFromCacheByToken($user->access_token);
        $tokens = $this->generatedTokens($inIp,$inAgent);
        $user->access_token = $tokens->access_token;
        $user->refresh_token = $tokens->refresh_token;
        $user->save();
        WorkerTokensFacade::putUserFromCacheByToken($tokens->access_token, $user);
        return (object) ['result' => $tokens, 'code' => 200];
    }
}