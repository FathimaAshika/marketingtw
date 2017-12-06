<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;



class MailModel extends Model {
    //var $availsubjects = array();    
    //public function setAvailSubjects($v) { $this->availsubjects = $v; }
    
    
    
    public function getStudentName($sid) {
        return DB::table('subject')->select('id','name')->where("id", $sid)->get();        
    }
    
    public function getCrmUserName($uid) {         
       return DB::table('system_user')->where("user_id", $uid)->value("name"); 
    }
    
    public function getStudentProperty( $sid, $prop) {
        return DB::table('students')->where("std_id", $sid)->value($prop); 
    }
    
    public function getPaymentTypes() {
        $data = DB::table('payment_type')->select('*')->get();
        return $this->queryToVarray($data, "id", "name");
    }
    
    public function getCurriculums() {  
        $data = DB::table('curriculum')->select('*')->get();
        return $this->queryToVarray($data, "id", "name");
    }
    
    
     public function getPaymentTerms() {
        $data = DB::table('package_type')->select('*')->get();
        return $this->queryToVarray($data, "id", "name");
    }
    
    
    public function getGrades() {  
        $data = DB::table('grade')->select('*')->get();
        return $this->queryToVarray($data, "id", "grade");
    }
    
    
    public function getStudentsByCurrAndGrade($grade_id, $curr_id ) {        
        $std_arr = array();
        $pidarr  = DB::table('package')
                   ->select('id')
                   ->where("curriculum_id", $curr_id )
                   ->where("grade_id", $grade_id )
                   ->get();
        $pidarr = $this->queryToVarray($pidarr, "id", "id" );
        if($pidarr) {
            $data = DB::table('students as s')
                    ->join('users as u', 'u.id', '=', 's.user_id')  
                    ->select('s.std_id','s.user_id','s.full_name','s.gender','s.grade','s.package_id')
                    ->whereIn("s.package_id", $pidarr ) 
                    ->where("u.status", 1 )  
                    ->get();
            $std_arr = $this->loadResultSet($data, "std_id");
        } 
        
        return $std_arr;
    }
    
    
    public function getStarsByStudentId($stu_id, $st_date, $end_date, $grade_id, $curr_id ) { 
       // DB::enableQueryLog();   
        $str = DB::table('student_stars')
                ->select('*')
                ->where("curriculum_id", $curr_id )
                ->where("grade_id",  $grade_id )
                ->where("student_id",   $stu_id )
                ->where("star_add_date", ">=",  $st_date )
                ->where("star_add_date", "<=",  $end_date )
                ->get();
        
        return $str;
    }
    
    
    public function getModuleScores($stu_id, $st_date, $end_date, $grade_id, $curr_id) {
       // DB::enableQueryLog();   
        $str = DB::table('student_stars')
                ->select('*')
                ->where("curriculum_id", $curr_id )
                ->where("grade_id",  $grade_id )
                ->where("student_id",   $stu_id )
                ->where("star_add_date", ">=",  $st_date )
                ->where("star_add_date", "<=",  $end_date )
                ->where( "stars_add_type", "MODULE_TEST" )
                ->orderBy("subject_id")
                ->get();    
       //  var_dump(DB::getQueryLog());exit;
        return $str;
    }
    
    
    public function getModuleDetails($mid) {
        return DB::table('module')->select('id','name')->where("id", $mid)->get();        
    }
    
    
    public function getLatestSubjectTests($stu_id, $subj_id, $st_date, $end_date, $grade_id, $curr_id) {
        //  DB::enableQueryLog();   
        $data = DB::table('quiz')
                ->select('*')
                ->where("curriculum_id", $curr_id )
                ->where("grade_id",  $grade_id )
                ->where("student_id",   $stu_id )
                ->where("subject_id",   $subj_id )
                ->where("status", "COMPLETED" )
                ->where("created_date", ">=",  $st_date )
                ->where("created_date", "<=",  $end_date )
                ->where( "type", "SUBJECT_TEST" )
                ->orderBy("created_date", "DESC")
                ->limit(20)
                ->get(); 
       // var_dump(DB::getQueryLog());exit;
        return $data;
    }
          
    
    public function getBestStarsWinningDay($stu_id, $st_date, $end_date, $grade_id, $curr_id ) { 
        $sql = "SELECT SUM(stars + time_bonus) as star_count, star_add_date "
                ." FROM student_stars WHERE curriculum_id = ".$curr_id
                ." AND grade_id =".$grade_id." AND student_id =".$stu_id
                ." AND star_add_date >='".$st_date."' AND star_add_date <= '".$end_date
                ."' GROUP BY star_add_date ORDER BY star_count DESC";
       return collect(\DB::select($sql))->first();
    }
   
    
    public function getAllTestDataByStudentId($stu_id, $st_date, $end_date, $grade_id, $curr_id) {
            $data = DB::table('quiz')
                ->select('*')
                ->where("curriculum_id", $curr_id )
                ->where("grade_id",  $grade_id )
                ->where("student_id",   $stu_id )
                ->where("status", "COMPLETED" )
                ->where("created_date", ">=",  $st_date )
                ->where("created_date", "<=",  $end_date )
                ->orderBy("created_date", "DESC")
                ->get(); 
        return $data;
    }
         
    
    public function getBestTestByStudentId($stu_id, $st_date, $end_date, $grade_id, $curr_id) {
       // DB::enableQueryLog();         
        $sql = "SELECT *, (schedule_time- time_taken) as time_val FROM quiz WHERE curriculum_id=".$curr_id
                ." AND grade_id =".$grade_id." AND student_id=".$stu_id
                ." AND status='COMPLETED' AND created_date >='".$st_date
                ."' AND created_date <='".$end_date."' ORDER BY full_marks DESC, time_val DESC" ;
       // print_r($sql) ;
        //var_dump(DB::getQueryLog());exit;
        return collect(\DB::select($sql))->first();
    } 
     
    
    public function getNotificationList($dt) {
        //DB::enableQueryLog();   
       $data = DB::table('transaction')
              ->select('*')
              ->where("expire_date", $dt )
              ->get();
      // var_dump(DB::getQueryLog());
       return $data;
    }
    
    
    public function getStudentDetails($stid) {
       $data = DB::table('users as u')
               ->join('students as s', 's.user_id', '=', 'u.id') 
               ->select('s.*', 'u.email')
               ->where("s.std_id", $stid)
               ->where("u.status", "1")
               ->get();
       return $data;
    }
                        
            
    public function getAllSubjectTests($stu_id, $st_date, $end_date, $grade_id, $curr_id) {
         // DB::enableQueryLog();   
        $d = DB::table('quiz')
             ->select('*')
             ->where("curriculum_id",$curr_id )
             ->where("grade_id",   $grade_id )
             ->where("student_id", $stu_id )
             ->where("status", "COMPLETED" )
             ->where("created_date", ">=", $st_date )
             ->where("created_date", "<=", $end_date )
             ->where( "type", "SUBJECT_TEST" )
             ->orderBy("created_date", "DESC")
             ->get(); 
        //var_dump(DB::getQueryLog());
        return $d;
    }
    
    
    public function getAllModuleTests($stu_id, $st_date, $end_date, $grade_id, $curr_id) {
         // DB::enableQueryLog();   
        $d = DB::table('quiz')
             ->select('*')
             ->where("curriculum_id",$curr_id )
             ->where("grade_id",   $grade_id )
             ->where("student_id", $stu_id )
             ->where("status", "COMPLETED" )
             ->where("created_date", ">=", $st_date )
             ->where("created_date", "<=", $end_date )
             ->where( "type", "MODULE_TEST" )
             ->orderBy("created_date", "DESC")
             ->get(); 
       // var_dump(DB::getQueryLog());
        return $d;
    }
    
    
    public function loadResultSet($data, $key=null) {
        if($key) {
            $new_arr = array();
            foreach( $data as $k=>$v ) 
                $new_arr[$v->$key] = $v;            
            $data = $new_arr;
        }
        return $data;
    }
    
    
    public function queryToVarray($arr, $key, $val, $case = null ) {
        $varr = array();
        if(is_array($arr) && !empty($arr) ) {
            foreach( $arr as $a ) {
                if( $case == "UPPER") {
                   $varr[$a->$key] = strtoupper($a->$val);
                } elseif( $case == "LOWER" ) {
                   $varr[$a->$key] = strtolower($a->$val);                    
                } else {
                   $varr[$a->$key] = $a->$val; 
                }
            }
        }
        return $varr;
    }
    
    
   public function getPrizesByStudentId($stu_id, $st_date, $end_date, $grade_id, $curr_id) {
       //DB::enableQueryLog(); 
        $d = DB::table('student_prizes as p')
             ->select('p.*', 'c.subject_id', 'c.prize_type', 'c.price_start_date', 'c.price_end_date' )
                ->join('star_prize_config as c', 'c.id', '=', 'p.prize_conf_id') 
             ->where("p.curriculum_id",$curr_id )
             ->where("p.grade_id",     $grade_id )
             ->where("p.student_id",   $stu_id )
             ->where("p.created_date", ">=", $st_date )
             ->where("p.created_date", "<=", $end_date )
             ->where("p.rank", "<=", 25 )
             ->orderBy("p.created_date", "DESC")
             ->get(); 
        //var_dump(DB::getQueryLog());
        return $d;
    }    
   
   
    public function getBestTestByStudent($stu_id, $st_dt, $ed_dt, $grade_id, $curr_id) {        
          $data = DB::table('quiz')
             ->select('*')
             ->where("curriculum_id",$curr_id )    
             ->where("grade_id",   $grade_id )
             ->where("student_id", $stu_id )
             ->where("status", "COMPLETED" )
             ->where("created_date", ">=", $st_dt )
             ->where("created_date", "<=", $ed_dt )
             ->orderBy("full_marks", "DESC")
             ->orderBy("time_taken", "ASC") 
             ->first(); 
        //var_dump(DB::getQueryLog());
        return $data;    
    }
    
    
    public function getWorstTestByStudent($stu_id, $st_dt, $ed_dt, $grade_id, $curr_id) {
       $data = DB::table('quiz')
            ->select('*')
            ->where("curriculum_id",$curr_id )    
            ->where("grade_id",   $grade_id )
            ->where("student_id", $stu_id )
            ->where("status", "COMPLETED" )
            ->where("created_date", ">=", $st_dt )
            ->where("created_date", "<=", $ed_dt )
            ->orderBy("full_marks", "ASC")
            ->orderBy("time_taken", "DESC") 
            ->first();  
       return $data;
    }
    
    
    public function  getAllTestsByStudentId($stu_id, $st_dt, $ed_dt, $grade_id, $curr_id) {
          $data = DB::table('quiz')
            ->select('*')
            ->where("curriculum_id", $curr_id )    
            ->where("grade_id",      $grade_id )
            ->where("student_id",    $stu_id )
            ->where("created_date", ">=", $st_dt )
            ->where("created_date", "<=", $ed_dt )
            ->where("status", "COMPLETED" )
            ->get(); 
          return $data;
    }
   
    
    public function insertReviewEmailToken($data) {
        $res = DB::table("student_review_email")->insert($data);
        return $res; 
    }
     
    
    public function getReviewEmailTokenData($etoken, $review_type) {
        $data = DB::table("student_review_email")
                ->select("*")
                ->where("email_tocken", $etoken)
                ->where("review_type", $review_type)
                ->get();        
        return isset($data[0])?$data[0]:array(); 
    }
     
     
    public function updateReviewStatus($etkn) {
          $upd = array();
          $upd["viewed"] = "1";
          $upd["viewed_on"] = DB::raw('NOW()');
          $status = DB::table('student_review_email')
                    ->where('email_tocken', $etkn)
                    ->update($upd);
 	  return $status;           
    }
      
      
    public function getSubjectDetails($sid) {
        return DB::table('subject')->select('id','name')->where("id", $sid)->get();        
    }
    
    
    public function getSubjectTestCount($stu_id, $st_dt, $ed_dt, $grade_id, $curr_id) {        
        $sql = "SELECT COUNT(*) as suj_test_count,subject_id "
                ." FROM quiz WHERE student_id = ".$stu_id
                ." AND curriculum_id = ".$curr_id." AND grade_id = "
                .$grade_id." AND status = 'COMPLETED' "
                ." AND created_date >= '".$st_dt."' AND created_date <= '".$ed_dt
                ."' AND type = 'SUBJECT_TEST' GROUP BY subject_id"; 
       //var_dump(DB::getQueryLog());
       $data = DB::select($sql);
      
       return $data; 
    }
    
    
    public function getnModuleTestCount($stu_id, $st_dt, $ed_dt, $grade_id, $curr_id) {
         $sql = "SELECT COUNT(*) as mod_test_count, module_id   FROM quiz WHERE student_id = ".$stu_id
              ." AND curriculum_id = ".$curr_id." AND grade_id = ".$grade_id." AND status = 'COMPLETED' "
              ." AND created_date >= '".$st_dt."' AND created_date <= '".$ed_dt
              ."' AND type = 'MODULE_TEST' GROUP BY module_id"; 
         $data = DB::select($sql);
      
         return $data; 
    }
       
    
    public function getCompetencyTestData($stu_id, $st_date, $end_date, $grade_id, $curr_id) {
        $sql = "SELECT AVG(full_marks) as avg_marks, subject_id   FROM quiz WHERE student_id = ".$stu_id
              ." AND curriculum_id = ".$curr_id." AND grade_id = ".$grade_id." AND status = 'COMPLETED' "
              ." AND created_date >= '".$st_date."' AND created_date <= '".$end_date
              ."' AND type = 'SUBJECT_TEST' GROUP BY subject_id"; 
        
        return DB::select($sql);  
    }
        
    
    public function getLeaderBoardBySubject($subj_id, $st_date, $end_date, $grade_id, $curr_id) {        
        $sql = "SELECT student_id, SUM(TIME_TO_SEC(time_taken)) AS total_time,"
               ." SUM(speed_bonus + stars) as all_stars, SUM(full_marks) as total_marks"
               ." FROM quiz WHERE curriculum_id = ".$curr_id." AND grade_id = ".$grade_id
               ." AND created_date >= '".$st_date."' AND created_date <= '".$end_date."'"
               ." AND subject_id = ".$subj_id." GROUP BY student_id ORDER BY "
               ." total_marks DESC, total_time ASC";  
        $data = DB::select($sql);
        return $data;        
    }
    
    
    public function getOverallLeaderBoard($st_date, $end_date, $grade_id, $curr_id) {
        $sql = "SELECT student_id, SUM(TIME_TO_SEC(time_taken)) AS total_time,"
               ." SUM(speed_bonus + stars) as all_stars, SUM(full_marks) as total_marks"
               ." FROM quiz WHERE curriculum_id = ".$curr_id." AND grade_id = ".$grade_id
               ." AND created_date >= '".$st_date."' AND created_date <= '".$end_date."'"
               ." GROUP BY student_id ORDER BY total_marks DESC, total_time ASC";  
        $data = DB::select($sql);
        return $data;  
    }
    
    
    public function getReviewEmailListByStudentId($student_id) {
        return DB::table("studentSupport_email")
               ->select("email")
               ->where("status", "1")
               ->where("std_id", $student_id)
               ->where("email_type", "REV")
               ->get(); 
    }
    
    
    public function getCompletedTutorChal( $stu_id, $st_dt, $end_dt, $curr_id, $grade_id ) {
        $data = DB::table('tutor_challange_question as tq')
                ->select('tq.id as ts_qid', 'tq.start_date', 'tq.end_date', 'tq.unit_id', 
                       'ta.id as tc_aswid','ta.stars', 'ta.full_marks')
                ->join('tutor_challange_answer as ta', 'ta.question_id', '=', 'tq.id') 
                ->where("ta.student_id", $stu_id )
                ->where("ta.is_marked", "1" )
                ->where("ta.submit_status", "SUBMITTED" )
                ->where("tq.end_date", ">=", $st_dt )
                ->where("tq.end_date", "<=", $end_dt )
                ->get(); 
        return $data;
    }         
    
    
    public function getSubjectIdByUnitId($unit_id, $package_id ) {
      //  DB::enableQueryLog();   
        $module_id = DB::table("unit")->where("id", $unit_id)->value("module_id");        
        $subj_id   = DB::table('package_subject as ps')
                     ->join('package_subject_module as pm', 'pm.package_subject_id', '=', 'ps.id') 
                     ->where("pm.module_id", $module_id)
                     ->where("ps.package_id", $package_id)
                     ->value("ps.subject_id");
       // var_dump(DB::getQueryLog());
        return $subj_id;        
    }
    
    
}
