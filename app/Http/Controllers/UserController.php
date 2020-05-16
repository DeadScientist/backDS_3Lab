<?php

namespace App\Http\Controllers;

use App\Facades\WorkerTokensFacade;
use Illuminate\Http\Request;
use App\Facades\UserServicesFacade;

class UserController extends Controller
{
    public function login(Request $request) {
        $res = UserServicesFacade::loginUser(
            $request->input('email'),
            $request->input('password'),
            $request->getClientIp(),
            WorkerTokensFacade::prepareUserAgent($request->userAgent())
        );
        return response()->json($res->result,$res->code);
    }

    public function logout(Request $request) {
        $accessToken = WorkerTokensFacade::parseBearerToToken($request->header('Authorization'));
        $user = $this->getUser(
            WorkerTokensFacade::getUserByToken($accessToken)
        );
        $user->access_token = null;
        $user->refresh_token = null;
        $user->save();
        WorkerTokensFacade::deleteUserFromCacheByToken($accessToken);
        return response()->json("successful",200);
    }

    public function register(Request $request) {
        $res = UserServicesFacade::registerUser(
            $request->input('first_name'),
            $request->input('last_name'),
            $request->input('email'),
            $request->input('password'),
            $request->getClientIp(),
            WorkerTokensFacade::prepareUserAgent($request->userAgent())
        );
        return response()->json($res->result,$res->code);
    }

    public function refreshToken(Request $request) {
        $token = WorkerTokensFacade::parseBearerToToken($request->header('Authorization'));
        $explodeToken =  WorkerTokensFacade::explodeToken($token);
        if (time() - $explodeToken->time > 129600) {
            return response()->json((object)['status' => 'Время жизни токена вышло'], 409);
        }
        if ($explodeToken->ip !== $request->getClientIp()) {
            return response()->json((object)['status' => 'некорректный токен!'], 401);
        }
        if ($explodeToken->agent !== WorkerTokensFacade::prepareUserAgent($request->userAgent())) {
            return response()->json((object)['status' => 'некорректный токен!'], 401);
        }
        $res = UserServicesFacade::refreshTokens(
            $token,
            $explodeToken->ip,
            $explodeToken->agent
        );
        return response()->json($res->result,$res->code);
    }
}
