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


class AnnualReview extends Command  {
    use PortalSummary;
    
    protected $signature = 'sendmail:annualreview';
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
        $this->fl_comment("Anual Perfomanace Review sending emails Process started at ".$start_time);
        
        $curriculums = $this->mail_model->getCurriculums();
        $grades      = $this->mail_model->getGrades();
        $sty_dt      = Carbon::now()->startOfYear()->toDateTimeString();
        $edy_dt      = Carbon::now()->endOfYear()->toDateTimeString(); 
        
        foreach( $curriculums as $curr_id => $curr_name ) {
            foreach( $grades as $grade_id => $gradeName ) {
                $this->fl_comment("Starting...... for ".$curr_name." curriculum - ".$gradeName);
                $stdentArr = $this->mail_model->getStudentsByCurrAndGrade( $grade_id, $curr_id);
                
                if($stdentArr) {
                    foreach( $stdentArr as $stu_id => $stu_arr ) {
                        $yearSummary = array();
                        //Header Details
                        $this->fl_comment("----------------------------------------");
                        $this->fl_comment("Genarating Year report for ".$stu_arr->full_name);
                        $header_data["full_name"] = $stu_arr->full_name;
                        $header_data["title"]     = $stu_arr->full_name."'s Annual Review";
                        $header_data["year"]      = Carbon::now()->year;
                       
                        //Time on portal this month and most acvite day
                        $this->fl_comment("Calculating Time on Portal...");
                        $portal_time      = $this->getTimeOnPortal( $stu_id, $sty_dt, $edy_dt, $curr_id, $grade_id, $stu_arr->package_id );
                        $active_day       = $this->getMostActiveDay($stu_id, $sty_dt, $edy_dt, $curr_id, $grade_id); 
                        $portal_time["active_day"] = $active_day;
                        $monthly_time    = $this->getMonthlyTimeOnPortal($stu_id, $sty_dt, $edy_dt, $curr_id, $grade_id, $stu_arr->package_id ); 
                        $portal_time["monthly_wise"] = $monthly_time;
                        $best_month     = $this->getBestMonthPortalTime($monthly_time);
                        $portal_time["best_month"] = $best_month;
                        $this->fl_comment("Calculate Time on Portal Done!");                         
                        
                        //Best Streak 
                        $this->fl_comment("Calculating Stregth and Weekness..");
                        $best_streak = $this->getBestStreak($stu_id, $sty_dt, $edy_dt, $grade_id, $curr_id);
                        $this->fl_comment("Calculate Stregth and Weekness Done!"); 
                        
                        //Total stars for this week, Subject Stars, Best Stars winnig day
                        $this->fl_comment("Calculating Stars...");                       
                        $stars            = $this->mail_model->getStarsByStudentId($stu_id, $sty_dt, $edy_dt, $grade_id, $curr_id);
                        $avail_subj       = DB::select('CALL spGetPackageSubject(?)', array($stu_arr->package_id)); 
                        $star_arr         = $this->processStars($stars, $avail_subj);
                        $best_worst_stars = $this->getBestWorstMonths($stars);                        
                        $this->fl_comment("Calculate Stars Done!");
                        
                        //Prizes for Student
                        $this->fl_comment("Retriving Prizes..."); 
                        $this->setAvailSubjects($avail_subj);
                        $prizes = $this->getStudentPrizes($stu_id, $sty_dt, $edy_dt, $grade_id, $curr_id, $grades);
                        $this->fl_comment("Retriving Prizes Done!");                         
                        
                        //Speed bonus
                        $this->fl_comment("Calculating Speed Bonus..."); 
                        $speed_bonus = $this->getSpeedBonus($stu_id, $sty_dt, $edy_dt, $grade_id, $curr_id);
                        $this->fl_comment("Calculate Speed Bonus Done!"); 
                        
                        //Best and Worst Test
                        $this->fl_comment("Finding Best and Worst Test..."); 
                        $best_wost_test = $this->getBestWorstTest($stu_id, $sty_dt, $edy_dt, $grade_id, $curr_id);
                        $this->fl_comment("Finished finding Best and Worst Test!");
                        
                        //Most and Least taken test
                        $this->fl_comment("Finding Most and Least taken Test..."); 
                        $most_least_test = $this->getMostLeastTest($stu_id, $sty_dt, $edy_dt, $grade_id, $curr_id);
                        $this->fl_comment("Finished finding Most and Least taken Test!");
                        
                        
                       /*
                       $monthlySummary["speed_bonus"]   = $speed_bonus;
                       $monthlySummary["best_test"]     = $best_test;
                       $monthlySummary["subject_tests"] = $subj_test; 
                       $monthlySummary["stngth_wkness"] = $sw_data;
                       $monthlySummary["best_streak"]   = $best_streak;
                         */
                        
                        $yearSummary["header_data"]    = $header_data;
                        $yearSummary["portal_time"]    = $portal_time;
                        $yearSummary["stars_award"]    = $star_arr;
                        $yearSummary["best_streak"]    = $best_streak;  
                        $yearSummary["bst_wrst_stars"] = $best_worst_stars;
                        $yearSummary["prizes"]         = $prizes;
                        $yearSummary["speed_bonus"]  = $speed_bonus;
                        $yearSummary["bst_wst_test"] = $best_wost_test;
                      //print_r($yearSummary);
                        
                        
                    }
                } else {
                    $this->fl_comment("No Students found!");     
                }
                $this->fl_comment("Ended for ".$curr_name." curriculum - Grade ".$gradeName);                
            } //foreach( $grades as $grade_id => $gradeName ) {
        } // foreach( $curriculums as $curr_id => $curr_name ) {
        
        
        
        
        /*
        
        
            
                 
                
                
                    
                   
                       
                       
                       
                       
                       
                       //Total stars for this week, Subject Stars, Best Stars winnig day
                       $this->fl_comment("Calculating Stars...");                       
                       $stars         = $this->mail_model->getStarsByStudentId($stu_id, $stmt_dt, $edmt_dt, $grade_id, $curr_id);
                       $avail_subj    = DB::select('CALL spGetPackageSubject(?)', array($stu_arr->package_id)); 
                       $best_star_day = $this->mail_model->getBestStarsWinningDay($stu_id, $stmt_dt, $edmt_dt, $grade_id, $curr_id );
                       $star_arr      = $this->processStars($stars, $avail_subj); 
                       $best_star_dte = isset($best_star_day->star_add_date) ? $best_star_day->star_add_date : '';
                       $best_star_cnt = isset($best_star_day->star_count)    ? $best_star_day->star_count    :  0;
                       $star_arr["best_star_cnt"] = $best_star_cnt;
                       if( $best_star_dte ) {
                           $star_arr["best_star_day"] = Carbon::parse("")->format('l');  
                       } else {
                           $star_arr["best_star_day"] = '';
                       }
                       $this->fl_comment("Calculate Stars Done!");                        
                       
                       
                       //Module Test Scores
                       $this->fl_comment("Calculating Module Test Summary...");  
                       $mod_scores = $this->mail_model->getModuleScores($stu_id, $stmt_dt, $edmt_dt, $grade_id, $curr_id);
                       $mod_scor   = $this->processModuleTestData($mod_scores, $avail_subj); 
                       $this->fl_comment("Calculate Module Test Summary Done!");
                       
                       
                       //Subject Test Scores
                       $this->fl_comment("Calculating Subject Test Results..."); 
                       $this->setAvailSubjects($avail_subj);
                       $subj_test = $this->getSubjectsTestResults($stu_id, $stmt_dt, $edmt_dt, $grade_id, $curr_id);
                       $this->fl_comment("Calculate Subject Test Results Done!"); 
                     
                       //Speed bonus
                       $this->fl_comment("Calculating Speed Bonus..."); 
                       $speed_bonus = $this->getSpeedBonus($stu_id, $stmt_dt, $edmt_dt, $grade_id, $curr_id);
                       $this->fl_comment("Calculate Speed Bonus Done!"); 
                       
                       //Best Test for student
                       $this->fl_comment("Finding best test...");                        
                       $best_test = $this->getBestTest($stu_id, $stmt_dt, $edmt_dt, $grade_id, $curr_id);                                          
                       $this->fl_comment("Finished Finding best test!");  
                       
                       //Strength and Weekness
                       $this->fl_comment("Calculating Stregth and Weekness..");
                       $sw_data = $this->getStrengthWeekness($stu_id, $stmt_dt, $edmt_dt, $grade_id, $curr_id);
                       $this->fl_comment("Calculate Stregth and Weekness Done!");                        
                       
                       //Strength and Weekness
                       $this->fl_comment("Calculating Stregth and Weekness..");
                       $best_streak = $this->getBestStreak($stu_id, $stmt_dt, $edmt_dt, $grade_id, $curr_id);
                       $this->fl_comment("Calculate Stregth and Weekness Done!");
                       
                       //-----------------------------------------
                       
                       
                       $st_data   = $this->mail_model->getStudentDetails($stu_id);                            
                       $stud_data["email"]        = $st_data[0]->email;
                       $stud_data["std_id"]       = $st_data[0]->std_id;
                       $stud_data["gender"]       = $st_data[0]->gender;
                       $stud_data["reference"]    = $st_data[0]->reference;               
                       $stud_data["full_name"]    = $st_data[0]->full_name;                              
                       
                       $monthlySummary["student_details"] = $stud_data;
                       
                       
                       $this->fl_comment("Sending email..."); 
                       $succ    = $this->sendMonthlyReviewEmail($monthlySummary);
                       $reason  = "MONTHLY_REVIEW";
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
                   
                
            
        
        
         * 
        
    }*/
}


}
