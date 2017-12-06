<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use PDF;
use App\Traits\PortalSummary;
use DB;
use App\Student;
use Carbon\Carbon;
use App\MailModel;
use App\PerformReviewModel;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\Classic;
use DateTime;
class NotificationEmails extends Command
{
    protected $signature   = 'sendmail:notification';
    protected $description = 'Command description';
 
    public function __construct( MailModel $mail_model ) {
       parent::__construct();                  
       $this->mail_model = new $mail_model;  
    }
    
    public function fl_comment($msg) {
       echo  "\n$msg";      
    }
    
    
    public function sendPaymentNotificationEmail($stud) { 
        $stat = Mail::send('emails.payment_notif', $stud, function($message) use ($stud) {
                $email_from = config("constants.PAYMENT_NOTIF_FROM");
                $message->from($email_from, "Tutor Wizard Info");
                $message->subject('Payment Reminder');
                $message->to($stud["email"]);
                //$message->to("lakshikasur@gmail.com");
        });        
        return $stat;
    }
    
    
    public function handle() {        
        $start_time = Carbon::now()->toDateTimeString(); 
        $this->fl_comment("Sending Payment Notification emails Process started at ".$start_time);       
        $dt         = Carbon::now()->addDays(10)->toDateString();
        $notif_arr  = $this->mail_model->getNotificationList($dt); 
        
        if($notif_arr) {
           foreach( $notif_arr as $notf ) { 
               $st_data   = $this->mail_model->getStudentDetails($notf->student_id);               
               $package   = DB::select('CALL spGetPackageById(?)', array($st_data[0]->package_id));
               $course = explode("-", $package[0]->name);
               $stud_data["curriculum"] = $course[0];                
               $stud_data["grade"]      = $course[1];
               $stud_data["stream"]     = $course[2];
               
               $stud_data["payment_amount"] = $notf->paid_amount - $notf->balance;
               $stud_data["payment_term"]   = $notf->payment_term;               
      
               $stud_data["first_name"]   = $st_data[0]->firstName;
               $stud_data["email"]        = $st_data[0]->email;
               $stud_data["std_id"]       = $st_data[0]->std_id;
               $stud_data["gender"]       = $st_data[0]->gender;
               $stud_data["reference"]    = $st_data[0]->reference;               
               $stud_data["full_name"]    = $st_data[0]->full_name;                              
               $stud_data["package_name"] = $package[0]->name;
               $stud_data["payment_date"] = $dt;
               
               $this->fl_comment("Sending email to ".$st_data[0]->full_name."..."); 
               $succ = $this->sendPaymentNotificationEmail($stud_data);
               
               $stud_id = $st_data[0]->std_id;
               $reason  = "PAYMENT_NOTIFICATION";
               $email   = $st_data[0]->email;               
               $creator = "SYSTEM";
               if($succ) {                   
                   $status  = "SENT";
                   $this->fl_comment("Email sent.");
               } else {
                   $status  = "NEW";
                   $this->fl_comment("Email send failed.");
               }
               $m = DB::update("SELECT fnInsertEmailNotification('$stud_id','$reason','$email','$status', '$creator' )");  
           }
        } else {
           $this->fl_comment("No Records found."); 
        } 
        $end_time = Carbon::now()->toDateTimeString();
        $this->fl_comment("");
        $this->fl_comment("Sending Payment Notification emails Process ended at ".$end_time);
        
        //***********************************************************************************
        //***********************************************************************************
        //Deactivate Student Packeges due to payment////
        $this->fl_comment("\n");
        $p2_start_time = Carbon::now()->toDateTimeString(); 
        $this->fl_comment("Deactivating Student Packages Process started at ".$p2_start_time); 
        //Add by Ashika -24/04/2017
        $deactive_notif = array();
        
        $payment_dates = DB::table('payment')
                                ->select('std_id', 'next_payment_date','balance','amount','id')
                                ->get();                                         
              
        for ($i = 0; $i < sizeof($payment_dates); $i++) {
             
            if (!empty($payment_dates[$i])) {
                $exdate                     = new Carbon($payment_dates[$i]->next_payment_date);
                $balance                    =$payment_dates[$i]->balance;
                $student_id                 =$payment_dates[$i]->std_id;
                $pament_id                  =$payment_dates[$i]->id;
                
                
                $user                       =DB::table('students')->where('std_id',$student_id)
                                                ->get();
                
                $user_id                    =$user[0]->user_id;
                $student_ref                =$user[0]->reference;
                $name                       =$user[0]->firstName;
                $due_date                   =$exdate->format('d M Y');
                
                
                
                $email                      =DB::table('users')->where('id',$user_id)
                                                ->value('email') ;
                                              
                $pay_type                   =DB::table('users')->where('email',$email)->value('pay_type');
                 $final_expiry               = $exdate;
//                if($pay_type=='2'){
//                    
//                     $final_expiry               = $exdate;
//                }
//                else{
//                     $final_expiry               = $exdate->addDays(2);
//                }
                              
                $curriculum_details         = StudentController::getStudentDetails($email);
            
                $curriculum                 =!empty($curriculum_details) ? $curriculum_details[0]->Curriculumn : '';
                $grade                      =!empty($curriculum_details) ? $curriculum_details[0]->Grade : '';
                $package                    =!empty($curriculum_details) ? $curriculum_details[0]->Package : '';
                $package_price_id           = DB::table('student_package_price')
                                                ->where('student_id', $student_id)
                                                ->value('package_price_id');
                    
                $getAmount                  = DB::table('package_price')
                                                ->where('id', $package_price_id)
                                                ->get();
                $priceTypeId                = $getAmount[0]->package_type_id;
                $amount                     = $getAmount[0]->price;
                $package_price              = $amount;
                
               // start  of cheking expired 
                
                if ($final_expiry < Carbon::now() ) {
                     
                         $this->fl_comment("1st command   ");
                    
                    if($balance >= 0){
                        
//                         $deactivate_student = DB::table('payment')
//                                          ->where('std_id',$student_id)
//                                          ->update(['status'=>'2']); 
                         
                        // deactivate user 
                        
                     // for free trial users directly make to overdue others give two days 
                        
                       //  $this->fl_comment("final expiry  ".$final_expiry ." ---- current date " . Carbon::now()->addDays(2));
                         
                         $dateOne = $final_expiry->addDays(2)->format('Y-m-d');
                         $dateTwo = Carbon::now()->format('Y-m-d');
                         
                         // $this->fl_comment($dateOne ."---" .$dateTwo);
                          
                        if($pay_type=='2') {
                            
                             $deactivate_student = DB::table('payment')
                                          ->where('std_id',$student_id)
                                          ->update(['status'=>'2']); 
                             
                             
                        }
                        else {
                            if($dateOne == $dateTwo){
                                
                                $deactivate_student = DB::table('payment')
                                          ->where('std_id',$student_id)
                                          ->update(['status'=>'2']); 
                            }
                            else{
                                $deactivate_student = DB::table('payment')
                                          ->where('std_id',$student_id)
                                          ->update(['status'=>'3']); 
                            }
                            
                            
                        }
                        
                        
//                    $deactivate_student = DB::table('payment')
//                                          ->where('std_id',$student_id)
//                                          ->update(['status'=>'2']);                                                 
                    }
                    else
                        {
                        
                        $plus_balance = -1*$balance;
                        
                        if($plus_balance >= $amount){                            
                            
                            // activate another payment term                                       
                
                 if ($exdate) {
                                                         
                $no_of_months       = DB::table('package_type')
                                                ->where('id', $priceTypeId)->value('months');
                    
                $original_pay_term  = DB::table('package_type')
                                                ->where('id', $priceTypeId)->value('name');
                    
                $new_expire_date   = date('Y-m-d', strtotime($no_of_months . " months", strtotime($exdate)));                                      
                              
                
               
                $new_bal                = $balance+ $amount;
                
                $previous_payments      = $balance ;               
                 
               $amp                     = $amount + $balance;
                
            
                
                $tr_details                             =[];
                $tr_details['date']                     = date('Y-m-d');
                $tr_details['expire_date']              =$new_expire_date;
                
                $tr_details['valid_start']              =$exdate;
                
                $tr_details['valid_end']                =new DateTime($new_expire_date);
                
                $tr_details['prev_balance']             =$balance;
                $tr_details['package_amount']           =$amount;
                $tr_details['payment_term']             =$original_pay_term;
                
                $tr_details['paid_amount']              =0;
                $tr_details['balance']                  =$new_bal;
                $tr_details['student_id']               =$student_id;
                $tr_details['payment_method']           ='Balance';
                $tr_details['total_amount_payable']     = $amp;
                
                $tr_details['previous_payments']        =$previous_payments;
                
                $tr_details['curriculum']               = $curriculum;                
                $tr_details['grade']                    = $grade;                
                $tr_details['package']                  = $package;
                
                $date                                   =date('d-m-Y');
                
                  $payment_details                      = array('status' => '1', 'balance' => $new_bal
                        ,'next_payment_date'=>new DateTime($new_expire_date)
                        );
                  
                GeneralController::updatePaymentTable($payment_details,$student_id);
                 
                
                DB::statement("CALL spGetStudentFromMail('$email',@ref,@name,@curriculumn,@grade,@package,@package_type,@expire_date,@package_amount,@next_payment_amount)");
                
                $k                              = DB::select('select @ref as Student_id,@name as Name,@curriculumn as Curriculumn ,@grade as Grade,@package as Package ,@package_type as Package_type ,@expire_date as Expire_date ,@package_amount as Package_amount,@next_payment_amount as Next_payment');
                
                $paid_amount                    =$amount;
                
                GeneralController::newTransaction($tr_details);
              
                  Mail::send('auth.emails.auto_payment_received',
                          ['email' => $email, 
                           'paid_amount' => $paid_amount,
                            'k' => $k,
                             'date' => $date], function($message) use ($email, $paid_amount, $k) {
                                        $message
                                                ->to($email)
                                                ->subject('Payment Success');
                                    });
                }
                            
                            
                            
                        }
                        else{
                            // deactivate user 
                            
                             $deactivate_student = DB::table('payment')
                                          ->where('std_id', $payment_dates[$i]->std_id)
                                          ->update(['status'=>'2']);
                            
                        }
                    }
                    
                       if ($deactivate_student) {
                       $deactive_notif['student_id'] = $student_id;
                       
                     
            
                      // print_r($deactive_notif);
                         DB::table('action_log')->insert([
                            'entity_id'    => $pament_id,
                            'student_id'   => $student_id,
                            'type'         => 'Deactivate student ',
                            'curr_status'  => 'inactive',
                            'priv_status'  => 'active',
                            'created_date' => Carbon::now(),
                            'created_by'   =>  $student_id
                        ]); 
                        
                         if($pay_type=='2'){
                    
                       $view             ='auth.emails.deactivation_freetrial';
                                        }
                         else{
                             
                        $view             = 'auth.emails.deactivation';
                        
                        }
                
                        Mail::send($view, ['email' => $email,'name'=>$name,'student_ref'=>$student_ref,'package'=>$package,'due_date'=>$due_date,'package_price'=>$package_price], function($message) use ($email,$name,$student_ref,$package,$due_date,$package_price) {
                         $message
                        ->to($email)
                        ->subject('Deactivation ');
                        });
                        
                    }
                    
                  
                }
                                                                                              
                // end of cheking expired 
            }              
        } 
        
         if($deactive_notif) {
             foreach( $deactive_notif as $stu_id ) {
                 $student_data = DB::table('students')
                                 ->where('std_id', $stu_id)->get();
                 $notif_msg    = $student_data[0]->full_name."(".$student_data[0]->reference
                                    .") was deactivated due to payment delay.";
                 $c_date       = Carbon::now()->toDateTimeString();  
                 $link         = "";                 
                 //$rslt = DB::statement(('CALL spInsertNotification("'.$pament_id.'", "'.$link.'", @feedID, "'.$c_date.'")'));
                // $status  =  DB::select("select @st as status"); 
                $notif = array();
                $notif["notification"] = $notif_msg;
                $notif["createdAt"]    = $c_date; 
                $notifid = DB::table("feed")->insertGetId($notif);             
                $crm_users = DB::table('users')->select("id")->where('type',"4")->get();
                if($crm_users) {
                    foreach($crm_users as $uid ) {
                        $notif_user = array();
                        $notif_user["notification_id"] = $notifid;
                        $notif_user["user_id"]         = $uid->id;
                        DB::table("feed_user")->insertGetId($notif_user); 
                        //$rslt = DB::statement(('CALL spInsertNotificationUser("'.$uid->id.'", "'.$link.'", @content, @date )'));
                    }
                }
             }             
         } 
         
         
        //Finished by Ashika -24/04/2017
         
        $p2_end_time = Carbon::now()->toDateTimeString();
        $this->fl_comment("");
        $this->fl_comment("Deactivate Student Process ended at ".$p2_end_time);
        $this->info("Deactivate Student Process ended at ".$p2_end_time);
        
         
         // cron to send daily logged users 
        $this->fl_comment("\n");
        $p2_start_time = Carbon::now()->toDateTimeString(); 
        $this->fl_comment("Send Report of Daily logged users  Process started at ".$p2_start_time); 
        
         $users = Classic::logged_users();
         
         if($users){
             
             Mail::send('auth.emails.loggedusers',
                          ['users' => $users
                           ], function($message) use ($users) {
                                        $message
                                                ->to('msmr1986@gmail.com ')
                                                ->cc('riswan@tutorwizard.lk')
                                                ->subject('Logged List ');
                                    });
                                    
         }
              
                                    
                                    
        $p2_end_time = Carbon::now()->toDateTimeString();
        $this->fl_comment("");
        $this->fl_comment("Send Report of Daily logged users process ended at ".$p2_end_time);
      //  $this->info("Deactivate Student Process ended at ".$p2_end_time);
        
         
         // end of report sending daily logged users 
          $this->fl_comment("\n");     
          $this->fl_comment("Logout user process start "); 
        
        
         Classic::logoutusersCron();
         
         $this->fl_comment("Logout user process end");      
        // log out the users those who are logged in more than 24 hours 
        
         $this->fl_comment("set valid dates for test accounts process start");  
          $test_users = DB::table('users')->where('type','6')->get();
      
      foreach ($test_users as $t){
             $uid = $t->id ;
             $sid = DB::table('students')->where('user_id',$uid)
                     ->value('std_id');
            $payment =  StudentController::getPaymentStudent($sid);                                              
            $expire_date =   !empty($payment) ?$payment[0]->next_payment_date : '' ;
            $valid_start =   !empty($payment) ?$payment[0]->valid_start : '' ;
            $valid_end   =   !empty($payment) ?$payment[0]->valid_end : '' ;
            
            
             if($valid_end==date('Y-m-d')  &&  $valid_end != $expire_date){
                 
                 $data= array(
                     'valid_start'  =>  $valid_end ,
                     'valid_end'    =>  $expire_date);
                 
                 GeneralController::updatePaymentTable($data,$sid);                                 
             }
 
         }
         
         
         
         $this->fl_comment("set valid dates for test accounts process end");  
     
    }
}