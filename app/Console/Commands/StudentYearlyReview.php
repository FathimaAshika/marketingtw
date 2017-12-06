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


class StudentYearlyReview extends Command {
    use PortalSummary;
    
    protected $signature   = 'review:send_yearly';
    protected $description = 'Command description';

   
    public function __construct(PerformReviewModel $perform_model, MailModel $mail_model) {
        parent::__construct();                  
        $this->perform_model  = new $perform_model; 
        $this->mail_model     = new $mail_model;  
    }

    public function setAvailSubjects($v) { $this->availsubjects = $v; }  
    public function fl_comment($msg)   { echo "$msg\n";         }
    
    
    public function handle() {
        $start_time = Carbon::now()->toDateTimeString();
        $this->fl_comment("Anual Perfomanace Review sending emails Process started at ".$start_time);
        $curriculums = $this->mail_model->getCurriculums();
        $grades      = $this->mail_model->getGrades();
        $sty_dt      = Carbon::now()->startOfYear()->toDateTimeString();
        $edy_dt      = Carbon::now()->endOfYear()->toDateTimeString();        
        $rstart_date = Carbon::now()->startOfYear()->format('m/Y');
        $rend_date   = Carbon::now()->endOfYear()->format('m/Y');
        
        foreach( $curriculums as $curr_id => $curr_name ) {
           foreach( $grades as $grade_id => $gradeName ) {
               $this->fl_comment("Starting...... for ".$curr_name." curriculum - ".$gradeName);
               $stdentArr = $this->mail_model->getStudentsByCurrAndGrade( $grade_id, $curr_id);        
               if($stdentArr) {
                   foreach( $stdentArr as $stu_id => $stu_arr ) {
                      $st_data     = $this->mail_model->getStudentDetails($stu_id);
                      $this->fl_comment("----------------------------------------");
                      $this->fl_comment("Processing data for ".$stu_arr->full_name);                      
                      if($st_data) {
                          $email_token = $this->getReviewEmailToken($stu_id);
                          $emailData = array();
                          $emailData["email_tocken"]      = $email_token;
                          $emailData["review_start_date"] = $rstart_date;
                          $emailData["review_end_date"]   = $rend_date;
                          $emailData["email"]             = isset($st_data[0]->email)     ? $st_data[0]->email     : ""; 
                          $emailData["reference"]         = isset($st_data[0]->reference) ? $st_data[0]->reference : "";  
                          $emailData["full_name"]         = isset($st_data[0]->full_name) ? $st_data[0]->full_name : "";
                          $emailData["first_name"]        = isset($st_data[0]->firstName) ? $st_data[0]->firstName : "";
                         
                          $emailData["email_from"]    = config("constants.REVIEW_EMAIL_FROM");                              
                          //$emailData["email_subject"] = "Annual Review - ".$emailData["full_name"]; 
                          $emailData["email_subject"] = "Performance Review - ". $emailData["review_start_date"].' / '.$emailData["review_end_date"];   
                          $emailData["portal_url"]    = $this->getPortalUrl()."review/annual";
                          $view                       = "emails.review_annual";                           
                          
                          $emailData["email_to"]      = "lakshikasur@gmail.com";
                          $eml_succ = $this->sendReviewEmail($emailData, $view);
                          /*
                           $rev_email_list = $this->getReviewEmailList($stu_id);
                           if($rev_email_list) {
                               foreach( $rev_email_list as $rev_email ) {
                                  $emailData["email_to"] = $rev_email; 
                                  $this->sendReviewEmail($emailData, $view);
                               }
                           }                       
                          */                          
                          if($eml_succ) {
                               $token_data = array();                     
                               $token_data["student_id"]       = $stu_id;
                               $token_data["review_start_date"]= $sty_dt;
                               $token_data["review_end_date"]  = $edy_dt;
                               $token_data["grade_id"]         = $grade_id;
                               $token_data["curriculum_id"]    = $curr_id;
                               $token_data["created_date"]     = Carbon::now()->toDateTimeString();
                               $token_data["created_by"]       = "SYSTEM";
                               $token_data["email_tocken"]     = $email_token;
                               $token_data["review_type"]      = "ANNUAL_REVIEW";
                               $this->mail_model->insertReviewEmailToken($token_data);
                               
                               $reason  = "ANNUAL_REVIEW";
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
                   }
               } else {
                 $this->fl_comment("No Students found!");        
               }                             
               $this->fl_comment("Ended for ".$curr_name." curriculum - Grade ".$gradeName);   
               $this->fl_comment("\n"); 
           } //foreach( $grades as $grade_id => $gradeName ) {
        } // foreach( $curriculums as $curr_id => $curr_name ) {          
    }
    
    
    
}
