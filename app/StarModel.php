<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class StarModel extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table      = 'student_stars';
    protected $fillable   = ["id", 
                            "student_id",
                            "curriculum_id",
                            "grade_id",
                            "subject_id",
                            "module_id",
                            "stars",
                            "duration",
                            "stars_add_type",
                            "star_add_date"                           
                            ];  
      
    public function insert_studentStars($data) {
        $res = DB::table("student_stars")->insert($data);
        return $res;  
    }  
    
    
    public function getPrizeConfig() {
        //DB::enableQueryLog(); 
        $tday =  Carbon::now()->toDateString();
        $data = DB::table("star_prize_config")
                ->select('*')
                ->where("status", "ACTIVE")
                ->where("is_calculated", "0")               
                ->where("price_end_date", "<", $tday)
                ->get();
        //var_dump(DB::getQueryLog());
        return $data;         
    }
    
    
    
    
    
    
     public function getStarsByMaxMark($mark) {
       $res = DB::table("star_system")
             ->select("*")              
             ->where("max_marks", $mark ) 
             ->get();
        return $res;
    }
    
    public function deleteRawData($conf_id) {
        return DB::table('student_prizes_raw')->where('prize_conf_id', $conf_id)->delete();
    }
    
    
    
    //*************************************************************    
    public function getValidPrizeConfigs( $st_dt, $end_dt ) {         
        $data = DB::table("star_prize_config")
                ->select('*')
                ->where("status", "ACTIVE")
                ->where("is_calculated", "0")               
                //->where("price_end_date", ">=", $st_dt)
                ->where("price_end_date", "<=", $end_dt)
                ->get();
        return $data;
    }
    
    
    public function getQuizDataByForSubjectPrice($subj_id, $grade_id, $curr_id, $st_date, $end_date) { 
        //DB::enableQueryLog(); 
        $qdata = DB::table("quiz")
                ->select("*")
                ->where("subject_id", $subj_id )                
                ->where("grade_id", $grade_id )                
                ->where("curriculum_id", $curr_id )                
                ->where("end_time", "<=", $st_date )                
                ->where("end_time", ">=", $end_date )                
                ->where("status", "COMPLETED" )                
                ->get();
          //var_dump(DB::getQueryLog());
         // exit;
        return $qdata;      
    }
    
    
    public function getQuizDataByForGradePrice($grade_id, $curr_id, $ps_date, $pend_date) { 
       // DB::enableQueryLog(); 
        $qdata = DB::table("quiz")
                ->select("*")              
                ->where("grade_id", $grade_id )                
                ->where("curriculum_id", $curr_id )                
                ->where("end_time", "<=", $pend_date )                
                ->where("end_time", ">=", $ps_date )                
                ->where("status", "COMPLETED" )                
                ->get();
        //var_dump(DB::getQueryLog());
        return $qdata;        
    }
      
    
    public function getProcessedRankData($conf_id) {
       $arr = DB::table("student_prizes_raw")
             ->select("*")              
             ->where("prize_conf_id", $conf_id )                
             ->orderBy("total_marks", "desc")         
             ->orderBy("total_time_taken", "asc")
             ->get();
        return $arr;
    }
    
    
    
    public function getAllStarsByStudent( $stud_id, $grade_id, $curr_id) {
        $data = DB::table('student_stars')                    
                ->select('*' )
                ->where('student_id', $stud_id)  
                ->where('curriculum_id', $curr_id)  
                ->where('grade_id',   $grade_id) 
                ->whereIn('stars_add_type', config('globals.star_types'))             
                ->get();
        return $data;
    }
    
    
    public function getAllPortalStarsByStudent( $stud_id) {
        $data = DB::table('student_stars')                    
                ->select('*' )
                ->where('student_id', $stud_id) 
                ->whereIn('stars_add_type', config('globals.star_types'))             
                ->get();
        return $data;
    }
    
}
