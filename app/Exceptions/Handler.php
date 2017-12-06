<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Mail;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    /*
    public function report(Exception $e)
    {
        parent::report($e);
    }
    */
    
    
    public function report( \Exception $exception ) { 
//        parent::report($exception);        
//        Mail::send('emails.exception', ['e' => $exception], function($message) {
//            $message->to('lakshikasur@gmail.com')
//                   // ->cc('riswan@tutorwizard.lk')
//                    ->subject('Error Occured In Application');
//        });        
//    
//        return parent::report($exception);
    }
    
    
    
    
    
    
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    
    public function render($request, Exception $e)
    {
        return parent::render($request, $e);
    }
    
    /*
    public function render($request, Exception $exception)     {
        if ($exception instanceof \Tymon\JWTAuth\Exceptions\JWTException) { 
          $data=array('status'=>'401');
        //what happen when JWT exception occurs 
        }
        else if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) { 
          $data=array('status'=>'401','error'=>'Token Invalid');
        //what happen when JWT exception occurs 
        }
       else  if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) { 
           $data=array('status'=>'401','error'=>'Token Expired');
        //what happen when JWT exception occurs 
        }
        else{
            $data=array('status'=>'401');
        }
         return response()->json($data, 401);
    }
     
     */
}
