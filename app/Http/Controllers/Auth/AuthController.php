<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Student;
use App\TempStudent;
use DB;
use Illuminate\Support\Facades\Session as sess;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\Session;

class AuthController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Registration & Login Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles the registration of new users, as well as the
      | authentication of existing users. By default, this controller uses
      | a simple trait to add these behaviors. Why don't you explore it?

     */

use AuthenticatesAndRegistersUsers,
    ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    //protected $redirectTo = 'dashboard';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data) {
        return Validator::make($data, [
//                    'email' => 'required|email|max:255|unique:users',
//                    'password' => 'required|min:6|confirmed',
                    "title" => "required",
                    "first_name" => "required",
                    "family_name" => "required",
                    "gender" => "required",
                    "birth_day" => "required",
                    "birth_month" => "required",
                    "birth_year" => "required",
                    "nation" => "required",
                    "country" => "required",
                    "email" => "required",
                    "phone" => "required",
                    "grade" => "required",
                    "school" => "required",
                    "intake_month" => "required",
                    "school_type" => "required",
                    "found_through" => "required",
                    "username" => "required",
                    "password" => "required",
        ]);
    }

    protected function create($data) {
        $country_id = $data->country;
       $country_id=intval($country_id);
       $grade=intval($data->grade);
       if($data->gender=='Male'){
$gender='M';
       }
        else if($data->gender=='Female'){
          $gender='F';

        }
          else{
            $gender='N';

          }
        $token = md5(microtime());
        $dob = $data->day . '-' . $data->month . '-' . $data->year;
        $user = new User();
        $user->username = $data->username;
        $user->type = 'student';
        $user->status = 'not_active';
        $user->password = bcrypt($data->password);
        $user->save();
        $a = $user->id;
        sess::put('user_id', $a);
        $isValidCountry = DB::select('CALL  checkCountry(?)', array($country_id));
        if ($isValidCountry[0]->allowed == 'T') {
            DB::select(('CALL spAddStudent(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'), array
                (
                $country_id,
                $data->title,
                $gender,
                $dob,
                $data->nationality,
                $data->email,
                $data->phonenumber,
                $data->schoolname,
                $grade,
                $data->intake,
                $data->type,
                $data->aboutus,
                $data->firstname,
                $data->familyname,
                $a
            ));
            DB::select(('CALL  addTempUsers(?,?,?,?)'), array
                (
                  $a,
                $data->username,
                bcrypt($data->password),
                $token
            ));
        } else {
          DB::select(('CALL  spAddTempStudent(?,?,?,?,?,?,?,?,?,?,?,?,?,?)'), array
                (
                $country_id,
                $data->title,
                $gender,
                $dob,
                $data->nationality,
                $data->email,
                $data->phonenumber,
                $data->schoolname,
                $data->grade,
                $data->intake,
                $data->type,
                $data->aboutus,
                $data->firstname,
                $data->familyname,
            ));   
            DB::select(('CALL  addTempUsers(?,?,?,?)'), array
                 (
                   $a,
                 $data->username,
                bcrypt($data->password),
                $token
            ));
        }
        return $user;
    }
}
