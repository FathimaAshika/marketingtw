<?php

namespace App\Http\Middleware;

use Closure;
use DB;

class CRMMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $temp_token   = $request->token;   
        $user_id      = !empty($isLoggedUser) ? $isLoggedUser[0]->user_id : '' ;
        $isLoggedUser = DB::table('temp_users')->where('token',$temp_token)->get();
        $isCrmuser    = DB::table('users')->where('id',$user_id)->value('type');            
        if($isCrmuser=='4' && $user_id ) {            
           return $next($request);                 
        }  
        return response('Unauthorized.', 401);         
    }
    
}
