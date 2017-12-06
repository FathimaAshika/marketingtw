<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;

use DB;
use App\Student;
use Session;
use Carbon\Carbon;
use App\StarModel;
use App\PerformReviewModel;



class calculatePrize extends Command
{
    /*** The name and signature of the console command. */
    protected $signature = 'calc:student_prizes';

    /*** The console command description.     */
    protected $description = 'This process will calculate Prizes for all Students.';

    
    /*** Create a new command instance.     */
    public function __construct(PerformReviewModel $perform_model, StarModel $star_model ) {
        parent::__construct();                  
        $this->perform_model = new $perform_model; 
        $this->star_model     = new $star_model;  
    }
    
    /*** Execute the console command.   * @return mixed */    
    public function handle()  {       
        $td_start = Carbon::today()->startOfDay();
        $td_end   = Carbon::today()->endOfDay();         
        $prizeConf_data = $this->star_model->getValidPrizeConfigs( $td_start, $td_end );
             
        if( $prizeConf_data ) {
            foreach( $prizeConf_data as $conf ) {
                $quiz_arr = array(); 
                $curr_id  = $conf->curriculum_id;
                $grade_id = $conf->grade_id;
                $end_date = $conf->price_end_date;
                $str_date = $conf->price_start_date;
                //$pz_st_obj = new Carbon($conf->price_start_date);
                //$pz_st_dt  = $pz_st_obj->startOfDay()->toDateTimeString();               
                //$pz_end_obj = new Carbon($conf->price_end_date);
                //$pz_end_dt  = $pz_end_obj->endOfDay()->toDateTimeString(); 
                $pz_st_dt  = $str_date." 08:00:00";
                $pz_end_dt = $end_date." 20:00:00";
               
                if( $conf->prize_type == "SUBJECT_PRIZE" ) {
                   $subject_id  = $conf->subject_id;
                   $result_data = $this->star_model->getQuizDataByForSubjectPrice( $subject_id, $grade_id, $curr_id, $pz_st_dt, $pz_end_dt );  
                } elseif( $conf->prize_type == "GRADE_PRIZE" ) {
                   $result_data  = $this->star_model->getQuizDataByForGradePrice( $grade_id, $curr_id, $pz_st_dt, $pz_end_dt);  
                }   
             
                if($result_data) {                     
                    $formattedArr = array();
                    foreach( $result_data as $qz ) {
                        $formattedArr[$qz->student_id][$qz->id]["stars"]      = $qz->stars;
                        $formattedArr[$qz->student_id][$qz->id]["full_marks"] = $qz->full_marks;
                        $formattedArr[$qz->student_id][$qz->id]["speed_bonus"]= $qz->speed_bonus;
                        $formattedArr[$qz->student_id][$qz->id]["time_taken"] = $qz->time_taken;                             
                    }                    
                    $result_arr = array();
                    foreach( $formattedArr as $sid => $sdata ) {
                        $total_stars   = 0;
                        $total_marks   = 0;
                        $total_time    = 0;
                        $total_sbonus    = 0;                        
                        $total_all_stars = 0;

                        foreach( $sdata as $qid=>$qdata ) {
                            $total_stars    = $qdata["stars"] + $total_stars;
                            $total_marks    = $qdata["full_marks"] + $total_marks;
                            $total_time     = $this->time_to_seconds($qdata["time_taken"]) + $total_time; 
                            $total_sbonus   = $qdata["speed_bonus"] + $total_sbonus; 
                            $total_all_stars = $total_stars + $total_sbonus;                                                      
                        } 
                        $insert_data = array();
                        $insert_data["student_id"]        = $sid;
                        $insert_data["curriculum_id"]     = $curr_id;
                        $insert_data["grade_id"]          = $grade_id;
                        $insert_data["prize_conf_id"]     = $conf->id;
                        $insert_data["total_marks"]       = $total_marks;
                        $insert_data["total_time_taken"]  = $total_time;
                        $insert_data["total_stars"]       = $total_all_stars;
                        $insert_data["total_speed_bonus"] = $total_sbonus;
                        $insert_data["all_stars"]         = $total_stars;
                        $res = DB::table("student_prizes_raw")->insert($insert_data);
                    } 
                    
                    $processed_arr =  $this->star_model->getProcessedRankData($conf->id); 
                    $rank = 1;
                    foreach( $processed_arr as $priz ) {                           
                        $finalData = array();
                        $strow = $this->perform_model->getStudentsDataByIds($priz->student_id);
                        if($strow["user_id"]) {  
                            $finalData["student_id"]    = $priz->student_id; 
                            $finalData["curriculum_id"] = $priz->curriculum_id;                        
                            $finalData["grade_id"]      = $priz->grade_id;                        
                            $finalData["prize_conf_id"] = $priz->prize_conf_id;                        
                            $finalData["total_marks"]   = $priz->total_marks;
                            $finalData["rank"]          = $rank;
                            $finalData["created_by"]    = "SYS";                        
                            $finalData["created_date"]  = Carbon::now()->toDateTimeString();
                            $finalData["all_stars"]     = $priz->all_stars;                          
                            $finalData["total_stars"]   = $priz->total_stars;                        
                            $finalData["total_time_taken"]  = $priz->total_time_taken;                        
                            $finalData["total_speed_bonus"] = $priz->total_speed_bonus;  
                            $rank++;
                            $res = DB::table("student_prizes")->insert($finalData);
                        }
                    }
                    $is_del = $this->star_model->deleteRawData($conf->id); 
               }   
           }
            $upd = array();               
            $upd["is_calculated"] = "1";  
            $upd["status"] = "COMPLETED";
            $is_upd = DB::table('star_prize_config')
                     ->where('id', $conf->id)                              
                     ->update( $upd );
        } else {
            $this->info('No Active Configuration found.');
        }
    }
    
    /*
    public function handle()  {
        $prizeConf = $this->star_model->getPrizeConfig();  
        if( $prizeConf ) {
            foreach( $prizeConf as $conf ) {
                $curr_id     = $conf->curriculum_id;
                $grade_id    = $conf->grade_id;              
                $pstart_date = $conf->price_start_date;
                $pend_date   = $conf->price_end_date;                        
                $quiz_arr    = array();                
                
                if( strtotime(Carbon::now()->toDateTimeString()) >= strtotime($conf->price_end_date)) {
                    if( $conf->prize_type == "SUBJECT_PRIZE" ) {
                        $subject_id = $conf->subject_id;
                        $quiz_arr  = $this->star_model->getQuizDataByForSubjectPrice($subject_id, $grade_id, $curr_id, $pstart_date, $pend_date);  
                    } elseif( $conf->prize_type == "GRADE_PRIZE" ) {
                        $quiz_arr  = $this->star_model->getQuizDataByForGradePrice($grade_id, $curr_id, $pstart_date, $pend_date);  
                    } 
                    
                    if($quiz_arr) {
                        $formattedArr = array();
                        foreach( $quiz_arr as $qz ) {
                            $formattedArr[$qz->student_id][$qz->id]["stars"]      = $qz->stars;
                            $formattedArr[$qz->student_id][$qz->id]["full_marks"] = $qz->full_marks;
                            $formattedArr[$qz->student_id][$qz->id]["speed_bonus"]= $qz->speed_bonus;
                            $formattedArr[$qz->student_id][$qz->id]["time_taken"] = $qz->time_taken;                             
                        }

                        $result_arr = array();
                        foreach( $formattedArr as $sid => $sdata ) {
                            $total_stars   = 0;
                            $total_marks   = 0;
                            $total_time    = 0;
                            $total_sbonus  = 0;                        
                            $total_all_stars = 0;

                            foreach( $sdata as $qid=>$qdata ) {
                                $total_stars    = $qdata["stars"] + $total_stars;
                                $total_marks    = $qdata["full_marks"] + $total_marks;
                                $total_time     = $this->time_to_seconds($qdata["time_taken"]) + $total_time; 
                                $total_sbonus   = $qdata["speed_bonus"] + $total_sbonus; 
                                $total_all_stars = $total_stars + $total_sbonus;                                                      
                            } 
                            $insert_data = array();
                            $insert_data["student_id"]        = $sid;
                            $insert_data["curriculum_id"]     = $curr_id;
                            $insert_data["grade_id"]          = $grade_id;
                            $insert_data["prize_conf_id"]     = $conf->id;
                            $insert_data["total_marks"]       = $total_marks;
                            $insert_data["total_time_taken"]  = $total_time;
                            $insert_data["total_stars"]       = $total_all_stars;
                            $insert_data["total_speed_bonus"] = $total_sbonus;
                            $insert_data["all_stars"]         = $total_stars;

                            $res = DB::table("student_prizes_raw")->insert($insert_data);
                        } 

                        $processed_arr =  $this->star_model->getProcessedRankData($conf->id); 
                        $rank = 0;
                        foreach( $processed_arr as $priz ) {
                            $rank++;
                            $finalData = array();
                            $finalData["student_id"]    = $priz->student_id; 
                            $finalData["curriculum_id"] = $priz->curriculum_id;                        
                            $finalData["grade_id"]      = $priz->grade_id;                        
                            $finalData["prize_conf_id"] = $priz->prize_conf_id;                        
                            $finalData["total_marks"]   = $priz->total_marks;
                            $finalData["rank"]          = $rank;
                            $finalData["created_by"]    = "SYS";                        
                            $finalData["created_date"]  = Carbon::now()->toDateTimeString();
                            $finalData["all_stars"]     = $priz->all_stars;                          
                            $finalData["total_stars"]   = $priz->total_stars;                        
                            $finalData["total_time_taken"] = $priz->total_time_taken;                        
                            $finalData["total_speed_bonus"] = $priz->total_speed_bonus;                       

                            $res = DB::table("student_prizes")->insert($finalData);
                        }                       
                        
                        $is_del = $this->star_model->deleteRawData($conf->id); 
                        if($is_del) {
                            $upd = array();               
                            $upd["is_calculated"] = "1";                                    
                            $is_upd = DB::table('star_prize_config')
                                     ->where('id', $conf->id)                              
                                     ->update( $upd );                    
                        }                        
                    }                      
                }                
           }
        } else {
            $this->info('No Active Configuration found.');
        }
    }
    */
    
    function time_to_seconds($time){        
        $split_time = explode(':', $time);
        $modifier = pow(60, count($split_time) - 1);
        $seconds = 0;
        foreach($split_time as $time_part){
            $seconds += ($time_part * $modifier);
            $modifier /= 60;
        }
        return $seconds;
    }
}
