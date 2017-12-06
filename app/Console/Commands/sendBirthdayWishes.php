<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use DB;
use App\Student;
use Session;
use Carbon\Carbon;



class sendBirthdayWishes extends Command {
    
    protected $signature   = 'send:birthdaywishes';    
    protected $description = 'Command description';

    
    public function __construct( Student $student_model ) {
        parent::__construct();
        $this->student_model = new $student_model; 
    }
    
    
    public function fl_comment($msg) {
       echo  "\n$msg";      
    }
    
    
    public function sendBirthdayWishEmail($stud) { 
        $stat = Mail::send('emails.stud_birthday', $stud, function($message) use ($stud) {
                $email_from = config("constants.PAYMENT_NOTIF_FROM");
                $message->from($email_from, "Tutor Wizard Info");
                $message->subject('Tutorwizards - Birthday Wishes');
                $message->to($stud["email"]);
                //$message->to("lakshikasur@gmail.com");
        });        
        return $stat;
    }    
        
    
    public function handle() {  
        
        
        //print_r(base_path());
       // exit;
        /*
         $data = array('name'=>"Lakshika De Silva");      
         Mail::send('mail', $data, function($message) {
            $message->to('lakshikasur@gmail.com', '')->subject('Laravel Testing Mail with Attachment');
            $message->from('info@tutorwizard.lk','Virat Gandhi');            
            $message->attach(base_path()."/.env");
        });
          
        */
        $start_time = Carbon::now()->toDateTimeString(); 
        $this->fl_comment("Sending Birthday Wishes emails Process started at ".$start_time);         
        
        $bthday_list = array();
        $stud_list   = $this->student_model->getStudentList();
        if( $stud_list ) {
            foreach( $stud_list as $st) {                
              if( $st->dob ) {                   
                  $dt    = Carbon::now(); 
                  $day   = $dt->day;
                  $month = $dt->month; 
                  $dt_arr  = explode("-", $st->dob);
                  
                  $dob_dt  = isset($dt_arr[2]) ? $dt_arr[2] : 0;
                  $dob_mnt = isset($dt_arr[1]) ? $dt_arr[1] : 0;
                  
                  if( ( (int)$dob_dt == $day ) && ( (int)$dob_mnt == $month) ) {
                      $bthday_list[] = $st;                      
                  }                    
              }                
            }     
            
            //print_r($bthday_list);
            
            if( $bthday_list ) {
                foreach( $bthday_list as $bs ) {                   
                    $stud = array();
                    $stud["email"]      = $bs->email;
                    $stud["reference"]  = $bs->reference;
                    $stud["full_name"]  = $bs->full_name;
                    $stud["first_name"]  = $bs->firstName;
                    $stud["familyName"] = $bs->familyName;
                    $stud["gender"]     = $bs->gender;
                    $stud["dob"]        = $bs->dob;
                    $stud["nation"]     = $bs->nation;                 
                    $succ = $this->sendBirthdayWishEmail($stud);
                    
                    $stud_id = $bs->std_id;
                    $reason  = "BIRTHDAY_WISHES";
                    $email   = $bs->email;               
                    $creator = "SYSTEM";
                    if($succ) {                   
                        $status  = "SENT";
                        $this->fl_comment("Email sent.");
                    } else {
                        $status  = "NEW";
                        $this->fl_comment("Email send failed.");
                    }
                    $m = DB::update("SELECT fnInsertEmailNotification('$stud_id','$reason','$email','$status','$creator')");  
                  }               
            } else {
                 $this->fl_comment("No Birthdays today");
            }
        } else {
           $this->fl_comment("No Students");
        }
        $stud_list = array();
        $end_time = Carbon::now()->toDateTimeString();
        $this->fl_comment("");
        $this->fl_comment("Sending Birthday Wishes emails Process ended at ".$end_time);
    }
}
