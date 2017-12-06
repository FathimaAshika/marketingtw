<?php

namespace app\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;
use DB;
class AuthenticateOnceWithBasicAuth
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
        $request_ip = $_SERVER['REMOTE_ADDR'];
        $isValidIp=DB::table('temp_users')->where('request_ip',$request_ip)->get();
        if($isValidIp && !empty($request_ip)){
            return $next($request);
        }
        return response('Unauthorized.', 401);
    }
}