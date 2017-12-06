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


class WeeklyMailout extends Command {
    
    use PortalSummary;
    
    protected $signature   = 'sendmail:weeklyreport';  
    protected $description = 'Command description';
    var $availsubjects     = array();

    
    
    public function __construct(PerformReviewModel $perform_model, MailModel $mail_model )    {
        parent::__construct();                  
        $this->perform_model = new $perform_model; 
        $this->mail_model    = new $mail_model;  
    }
    
    
    public function setAvailSubjects($v) { $this->availsubjects = $v; }
    
    
    public function fl_comment($msg) {  
        echo  "\n $msg";      
    }
            
     
     public function sendWeeklyReviewEmail($data) { 
        $stat = Mail::send('emails.test', $data, function($message) use ($data) {
                $email_from = config("constants.REVIEW_EMAIL_FROM");
                $email_to = $data["student_details"]["email"];
                $email_to = "lakshikasur@gmail.com";
                $message->from($email_from);
                $message->subject($data["header_data"]["title"]);
                $message->to($email_to);
        });        
        return $stat;
    }
   
    
            
   
    public function handle() { 
        $start_time = Carbon::now()->toDateTimeString(); 
        $this->fl_comment("Weekly Perfomanace Review sending emails Process started at ".$start_time);
        
        $curriculums = $this->mail_model->getCurriculums();
        $grades      = $this->mail_model->getGrades();
        $stwk_dt     = Carbon::now()->startOfWeek()->toDateTimeString();
        $edwk_dt     = Carbon::now()->endOfWeek()->toDateTimeString(); 
        
        foreach( $curriculums as $curr_id => $curr_name ) {
            foreach( $grades as $grade_id => $gradeName ) {
                $this->fl_comment("Starting...... for ".$curr_name." curriculum - ".$gradeName);
                $stdentArr = $this->mail_model->getStudentsByCurrAndGrade( $grade_id, $curr_id); 
                
                if($stdentArr) {
                    foreach( $stdentArr as $stu_id => $stu_arr ) {
                       $weeklySummary = array();
                       
                       //Header Details
                       $this->fl_comment("----------------------------------------");
                       $this->fl_comment("Genarating Weekly report for ".$stu_arr->full_name);
                       $header_data["full_name"]  = $stu_arr->full_name;
                       $header_data["title"]      = $stu_arr->full_name."'s Weekly Review";
                       $header_data["start_date"] = Carbon::now()->startOfWeek()->toDateString();
                       $header_data["end_date"]   = Carbon::now()->endOfWeek()->toDateString();
                       
                       //Time on portal this week and most acvite day
                       $this->fl_comment("Calculating Time on Portal...");
                       $portal_time = $this->getTimeOnPortal( $stu_id, $stwk_dt, $edwk_dt, $curr_id, $grade_id, $stu_arr->package_id );
                       $active_day  = $this->getMostActiveDay($stu_id, $stwk_dt, $edwk_dt, $curr_id, $grade_id); 
                       $portal_time["active_day"] = $active_day;
                       $this->fl_comment("Calculate Time on Portal Done!");   
                       
                       //Total stars for this week, Subject Stars, Best Stars winnig day
                       $this->fl_comment("Calculating Stars...");                       
                       $stars         = $this->mail_model->getStarsByStudentId($stu_id, $stwk_dt, $edwk_dt, $grade_id, $curr_id);
                       $avail_subj    = DB::select('CALL spGetPackageSubject(?)', array($stu_arr->package_id)); 
                       $best_star_day = $this->mail_model->getBestStarsWinningDay($stu_id, $stwk_dt, $edwk_dt, $grade_id, $curr_id );
                       $star_arr      = $this->processStars($stars, $avail_subj); 
                       $best_star_dte = isset($best_star_day->star_add_date) ? $best_star_day->star_add_date : '';
                       $best_star_cnt = isset($best_star_day->star_count)     ? $best_star_day->star_count    :  0;
                       $star_arr["best_star_cnt"] = $best_star_cnt;
                       if( $best_star_dte ) {
                           $star_arr["best_star_day"] = Carbon::parse("")->format('l');  
                       } else {
                           $star_arr["best_star_day"] = '';
                       }                      
                       $this->fl_comment("Calculate Stars Done!"); 
                      
                       //Module Test Scores
                       $this->fl_comment("Calculating Module Test Summary...");  
                       $mod_scores = $this->mail_model->getModuleScores($stu_id, $stwk_dt, $edwk_dt, $grade_id, $curr_id);
                       $mod_scor   = $this->processModuleTestData($mod_scores, $avail_subj); 
                       $this->fl_comment("Calculate Module Test Summary Done!");
                       
                       //Subject Test Scores
                       $this->fl_comment("Calculating Subject Test Results..."); 
                       $this->setAvailSubjects($avail_subj);
                       $subj_test = $this->getSubjectsTestResults($stu_id, $stwk_dt, $edwk_dt, $grade_id, $curr_id);
                       $this->fl_comment("Calculate Subject Test Results Done!"); 
                     
                       //Speed bonus
                       $this->fl_comment("Calculating Speed Bonus..."); 
                       $speed_bonus = $this->getSpeedBonus($stu_id, $stwk_dt, $edwk_dt, $grade_id, $curr_id);
                       $this->fl_comment("Calculate Speed Bonus Done!"); 
                       
                       //Best Test for student
                       $this->fl_comment("Finding best test...");                        
                       $best_test = $this->getBestTest($stu_id, $stwk_dt, $edwk_dt, $grade_id, $curr_id);                                          
                       $this->fl_comment("Finished Finding best test!");                        
                       
                        //Strength and Weekness
                       $this->fl_comment("Calculating Stregth and Weekness..");
                       $sw_data = $this->getStrengthWeekness($stu_id, $stwk_dt, $edwk_dt, $grade_id, $curr_id);
                       $this->fl_comment("Calculate Stregth and Weekness Done!"); 
                                              
                       $weeklySummary["header_data"]   = $header_data; 
                       $weeklySummary["portal_time"]   = $portal_time;
                       $weeklySummary["stars_award"]   = $star_arr;
                       $weeklySummary["module_score"]  = $mod_scor;
                       $weeklySummary["subject_tests"] = $subj_test;
                       $weeklySummary["speed_bonus"]   = $speed_bonus;
                       $weeklySummary["best_test"]     = $best_test;
                       $weeklySummary["stngth_wkness"] = $sw_data;
                       
                       
                       $st_data   = $this->mail_model->getStudentDetails($stu_id);                            
                       $stud_data["email"]        = isset($st_data[0]->email) ? $st_data[0]->email :"";
                       $stud_data["std_id"]       = isset($st_data[0]->std_id) ? $st_data[0]->std_id :"";
                       $stud_data["gender"]       = isset($st_data[0]->gender) ? $st_data[0]->gender :"";
                       $stud_data["reference"]    = isset($st_data[0]->reference) ? $st_data[0]->reference:"";               
                       $stud_data["full_name"]    = isset($st_data[0]->full_name) ? $st_data[0]->full_name: "";                              
                       
                       $weeklySummary["student_details"] = $stud_data;
                       
                       
                      
                       
                if( $stu_id == 109) {
                       $this->fl_comment("Sending email..."); 
                       $succ    = $this->sendWeeklyReviewEmail($weeklySummary);
                       $reason  = "WEEKLY_REVIEW";
                       $email   = $stud_data["email"]; 
                       $creator = "SYSTEM";
                       if($succ) {                   
                            $status  = "SENT";
                            $this->fl_comment("Email sent.");
                        } else {
                            $status  = "NEW";
                            $this->fl_comment("Email send failed.");
                        }   
                       $m = DB::update("SELECT fnInsertEmailNotification('$stu_id','$reason','$email','$status', '$creator' )");  
                       
                       
                }
                       
                       
                       
                       
                       
                       $this->fl_comment("Genarating PDF Report...");  
                       $data      = $weeklySummary;
                       $file_name = "weekly_report.pdf";
                       $pdf       = PDF::loadView('emails.test', $data)->save("temp_dir/weekly_report.pdf");
                       $data["file_name"] = "temp_dir/weekly_report.pdf";
                       
                     
                        /* 
                        * $this->fl_comment("Sending email.....");                       
                       
                       Mail::send('weekly_mail', $data, function($message) use ($data) {
                          $message->to('lakshikasur@gmail.com');
                          $message->subject('Weekly Email');
                          $message->from('info@tutorwizard.com');
                          
                          $message->attach(base_path()."/".$data["file_name"], 
                                  array( 'mime' => 'application/pdf')
                                  );
                       });
                       */
                    }                    
                } else {
                    $this->fl_comment("No Students found!");     
                }
                $this->fl_comment("Ended for ".$curr_name." curriculum - Grade ".$gradeName);                
            }//foreach( $grades as $grade_id => $gradeName ) {
        }// foreach( $curriculums as $curr_id => $curr_name ) {  
    }
}


