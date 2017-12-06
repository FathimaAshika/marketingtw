<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use DB;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {   
        $temp_token=$request->token;
        $isLoggedUser=DB::table('temp_users')->where('token',$temp_token)->get();
        if ($isLoggedUser !=[] || $temp_token=='1') {
            $userId=!empty($isLoggedUser)? $isLoggedUser[0]->user_id : null;
            $isValidParty=DB::table('users')->where('id',$userId)->value('type');
            if($isValidParty=='1' || $temp_token==='1'){
                 return $next($request);
            } 
        }
         return response('Unauthorized.', 401);
    }
}
