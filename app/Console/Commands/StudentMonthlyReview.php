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



class StudentMonthlyReview extends Command {
    use PortalSummary;
    
    
    protected $signature = 'review:send_monthly';
    protected $description = 'Command description';

    
    public function __construct(PerformReviewModel $perform_model, MailModel $mail_model) {
        parent::__construct();                  
        $this->perform_model = new $perform_model; 
        $this->mail_model    = new $mail_model;  
    }
    
    
    public function setAvailSubjects($v) { $this->availsubjects = $v; }  
    public function fl_comment($msg)   { echo "\n $msg";         }
    
    
    
    public function handle() {
        $start_time = Carbon::now()->toDateTimeString(); 
        $this->fl_comment("Monthly Perfomanace Review sending emails Process started at ".$start_time);
        $curriculums = $this->mail_model->getCurriculums();
        $grades      = $this->mail_model->getGrades();
        $stmt_dt     = Carbon::now()->startOfMonth()->toDateTimeString();
        $edmt_dt     = Carbon::now()->endOfMonth()->toDateTimeString();
        
        $rstart_date = Carbon::now()->startOfMonth()->format('d/m/Y');
        $rend_date   = Carbon::now()->endOfMonth()->format('d/m/Y');
                            
        foreach( $curriculums as $curr_id => $curr_name ) {
            foreach( $grades as $grade_id => $gradeName ) {
                $this->fl_comment("Starting...... for ".$curr_name." curriculum - ".$gradeName);
                $stdentArr = $this->mail_model->getStudentsByCurrAndGrade( $grade_id, $curr_id); 
                if($stdentArr) {
                    foreach( $stdentArr as $stu_id => $stu_arr ) {
                       $email_token = $this->getReviewEmailToken($stu_id);
                       $st_data     = $this->mail_model->getStudentDetails($stu_id);
                       if( $st_data ) {  
                            $emailData = array();
                            $emailData["email_tocken"]      = $email_token;
                            $emailData["review_start_date"] = $rstart_date;
                            $emailData["review_end_date"]   = $rend_date;                       
                            $emailData["email"]             = isset($st_data[0]->email)   ? $st_data[0]->email     : ""; 
                            $emailData["reference"]         = isset($st_data[0]->reference)? $st_data[0]->reference  : "";  
                            $emailData["full_name"]         = isset($st_data[0]->full_name)? $st_data[0]->full_name  : "";
                            $emailData["first_name"]        = isset($st_data[0]->firstName)? $st_data[0]->firstName  : "";
                            
                            $emailData["email_from"]    = config("constants.REVIEW_EMAIL_FROM");
                            $emailData["email_to"]      = $emailData["email"];
                            $emailData["email_subject"] = "Performance Review - ". $emailData["review_start_date"].' / '.$emailData["review_end_date"];   
                            //$emailData["email_subject"] = "Monthly Review - ".$emailData["full_name"];                        
                            $emailData["portal_url"]    = $this->getPortalUrl()."review/monthly";
                            $view                       = "emails.review_monthly";
                                                
                            
                                                         
                            
                            //$emailData["email_to"]      = "lakshikasur@gmail.com"; 
                            $eml_succ = $this->sendReviewEmail($emailData, $view);
                            
                            //exit;
                            
                            
                            
                            $rev_email_list = $this->getReviewEmailList($stu_id);
                            if($rev_email_list) {
                                foreach( $rev_email_list as $rev_email ) {
                                   $emailData["email_to"] = $rev_email; 
                                   $this->sendReviewEmail($emailData, $view);
                                }
                            }
                            
                            
                            
                            
                            if($eml_succ) {
                               $token_data = array();                     
                               $token_data["student_id"]       = $stu_id;
                               $token_data["review_start_date"]= $stmt_dt;
                               $token_data["review_end_date"]  = $edmt_dt;
                               $token_data["grade_id"]         = $grade_id;
                               $token_data["curriculum_id"]    = $curr_id;
                               $token_data["created_date"]     = Carbon::now()->toDateTimeString();
                               $token_data["created_by"]       = "SYSTEM";
                               $token_data["email_tocken"]     = $email_token;
                               $token_data["review_type"]      = "MONTHLY_REVIEW";                               
                               $this->mail_model->insertReviewEmailToken($token_data);                               
                               
                               $reason  = "MONTHLY_REVIEW";
                               $status  = "SENT";
                               $creator = "SYSTEM";
                               $stu_email = $emailData["email"];
                               DB::update("SELECT fnInsertEmailNotification('$stu_id','$reason','$stu_email','$status','$creator')");
                            } else {
                               $this->fl_comment("Email sent failed!");   
                            }                           
                       } else {
                          $this->fl_comment("Valid Student details are not avalible!");   
                       } 
                    }//foreach( $stdentArr as $stu_id => $stu_arr ) {
                } else {
                    $this->fl_comment("No Students found!");        
                }
                $this->fl_comment("Ended for ".$curr_name." curriculum - Grade ".$gradeName);  
            } //foreach( $grades as $grade_id => $gradeName ) {
        } // foreach( $curriculums as $curr_id => $curr_name ) { 
    }    
    
    
    
}
