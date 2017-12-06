<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use DB;

class CheckToken
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
        $temp_token     = $request->token;
        $isLoggedUser   = DB::table('temp_users')->where('token',$temp_token)->get();
        
        if ($isLoggedUser !=[]) {
           
                 return $next($request);
           
        }
        
        $data = array('status'=>'401','message'=>'Authorization required');
         return response($data,401);
    }
}
