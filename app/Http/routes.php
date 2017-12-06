<?php

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Mail;

use Carbon\Carbon;




header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: accept,content-type,x-xsrf-token');
header('Content-Type: application/json');


   
Route::post('user/add/details', 'AdminController@addUserDetails');

Route::get('start/competiton', 'AdminController@startCompetition');
Route::get('do/competition', 'AdminController@doCompetition');

Route::get('check/done/competition/{uid}', 'AdminController@insertCompetitionMarks');

Route::post('user/update/score/details', 'AdminController@updateCompetionMarks');

Route::get('end/competition', 'AdminController@endCompetion');

Route::get('/',function(){
    
    return view('welcome');
    // return view('welcomeform');
    
});


Route::get('/welcomeform',function(){
    
    //return view('welcome');
     return view('welcomeform');
    
});

Route::get('get/competition/users', 'AdminController@getCompetitionUsers');

Route::get('delete/user/{uid}', 'AdminController@removeMailDeviceNotSupport');
