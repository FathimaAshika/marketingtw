<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Http\Request;
use DB;
use Mail;
use Carbon\Carbon;
use App\User;
use Validator;
use Redirect;

class AdminController extends Controller {

    public function insertCompetitionMarks($userId) {

        $checkUser =User::find($userId);
        
        if ($checkUser != []) {
            $hasDone = $checkUser->competition_done;


            if ($hasDone == '1') {

                $data = array("status" => '401', "message" => "done already");
            } else {

                $data = array("status" => '200', "message" => "ok");
            }
        } else {
            $data = array("status" => '401', 'message' => 'no user there ');
        }
        return response($data, 200)->header('Content-Type', 'application/json');
    }

    public function updateCompetionMarks(Request $r) {



        $userId = $r->userid;
        $score = $r->correct_precentage;
        $time = $r->time_taken;
        $clicks = $r->clicks;

        $checkUser = User::find($userId);

        if ($checkUser) {

            $checkUser->score = $score;
            $checkUser->time_taken = $time;
            $checkUser->clicks = $clicks;
            $checkUser->competition_done = 1;
            $checkUser->update();

            $data = array("status" => '200');
        } else {

            $data = array("status" => '401');
        }

        return response($data, 200)->header('Content-Type', 'application/json');
    }

    public function addUserDetails(Request $r) {

        $email = $r->email;
        $fullname = $r->fullname;
        $mobile = $r->mobile;

//        $rules = [
//            'email'     => 'required|unique:users',
//            'fullname'  => 'required',
//            'mobile'    => 'required'
//        ];
//        $validator = Validator::make($r->all(), $rules);

//        if ($validator->fails()) {
//
//            return redirect()->back()
//                            ->withInput($r->all())
//                            ->withErrors($validator->errors()); // will return only the errors
//        }

        $add =User::create([
            'email' => $email,
            'fullname' => $fullname,
            'mobile' => $mobile
        ]);
        if ($add) {
            
            $id = $add->id;
            
            $data = array('status'=>'200','data'=>$id);

        } else {
            
            
             $data = array('status'=>'401');
        }
        
        return response($data,200)->header('Content-Type','application/json');
        
    }

    public function startCompetition() {

        return view('game.startCompetition');
    }

    public function doCompetition() {

        return view('game.competition');
    }

     public function endCompetion() {

        return view('game.endCompetition');
    }
    
    public function getCompetitionUsers() {
        
        $commonQuery =  DB::table('users')->orderBy('score','desc')
                ->orderBy('time_taken','asc')
                ->orderBy('clicks','asc')
                        ->get();
 
        if ($commonQuery) {  
            
            $data = array('status' => '200','data' => $commonQuery);
            
        } else {
            
            $data = array('status' => '401');
            
        }
        return response($data, 200)->header('Content-Type', 'application/json');
    }
    
    public function removeMailDeviceNotSupport($uid){
        
        $user = User::find($uid);
        
        
        if($user){
            
            $user->delete();
        }
        return Redirect::to('/');
        
    }

}
