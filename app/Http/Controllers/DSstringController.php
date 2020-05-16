<?php

namespace App\Http\Controllers;

use App\DSstring;
use App\Facades\WorkerTokensFacade;
use Illuminate\Http\Request;

class DSstringController extends Controller
{

    private function getUser($inToken)
    {
        return WorkerTokensFacade::getUserByToken(
            WorkerTokensFacade::parseBearerToToken($inToken)
        );
    }

    public function create(Request $request)
    {
        $first_name=$request->input('first_name');
        $last_name=$request->input('last_name');
        $age=(int)$request->input('age');
        $user = $this->getUser($request->header('Authorization'));
        if (isset($first_name) && isset($last_name) && isset($age)) {
            $result = DSstring::create([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'created_users_inj' =>  "" . $user->first_name . " " . $user->last_name,
                'age' => $age
            ]);
            return response()->json($result,200);
        }
        return response()->json('Не прислан текст или его длина меньше 5 символов',500);
    }

    public function getall (Request $request)
    {
        $created_users_inj = $request->input('created_users_inj');
        $result = DSstring::where('created_users_inj','like',$created_users_inj)->cursor();
        return response()->json($result,200);
    }

    public function delete (Request $request)
    {
        $id=(int)$request->input('id');
        if (isset($id)) {
            $result = DSstring::where('id',$id )->delete();
            if ($result>0)
            {
                return response()->json($result,200);
            }
            return response()->json('fail',500);
        }
        return response()->json('Не прислан id',500);
    }
}
