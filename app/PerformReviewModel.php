<?php

namespace App;
use DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class PerformReviewModel extends Model  {
    
    private $sw_test_count = 10;
    var $prize_conf_status = array("ACTIVE", 
                                   "COMPLETED");
    
    public function getPrizeConfById( $conf_id ) {
        $data = DB::table('star_prize_config')                
               ->select('*')   
               ->where('id', $conf_id)             
               ->where('status', "ACTIVE")              
               ->get();
        return $data;
    }
     
    
    public function getPrizeData( $subj_id, $cfg_sdt, $cfg_edt, $grade_id, $curr_id ) { 
        $cfg_sdt = $cfg_sdt." 08:00:00"; 
        $cfg_edt = $cfg_edt." 20:00:00";              
        if( strtoupper($subj_id) == "ALL" ) {
             $sqlpart = "";  
        } else {
             $sqlpart = " AND subject_id= ".$subj_id;
        }
        $sql = "SELECT *, student_id, SUM(TIME_TO_SEC(duration)) AS total_time, "
                 ." SUM(time_bonus + stars) as all_stars FROM student_stars "   
                 ." WHERE curriculum_id = ".$curr_id." AND grade_id = ".$grade_id.$sqlpart
                 ." AND star_add_date >='".$cfg_sdt."' AND star_add_date <= '".$cfg_edt."' "
                 ." AND stars_add_type in ('SUBJECT_TEST','TUTOR_CHALLENGE','MODULE_TEST')"
                 ." GROUP BY student_id ORDER BY all_stars DESC, total_time ASC"; 
        $data = DB::select($sql);       
        return $data; 
    }  
    
    
    public function getProfilePrizeData( $subj_id, $cfg_sdt, $cfg_edt, $grade_id, $curr_id, $student_id ) { 
        $cfg_sdt = $cfg_sdt." 08:00:00"; 
        $cfg_edt = $cfg_edt." 20:00:00";              
        if( strtoupper($subj_id) == "ALL" ) {
             $sqlpart = "";  
        } else {
             $sqlpart = " AND subject_id= ".$subj_id;
        }
        $sql = "SELECT *, student_id, SUM(TIME_TO_SEC(duration)) AS total_time, "
                 ." SUM(time_bonus + stars) as all_stars FROM student_stars "   
                 ." WHERE student_id = ".$student_id." AND curriculum_id = ".$curr_id
                 ." AND grade_id = ".$grade_id.$sqlpart
                 ." AND star_add_date >='".$cfg_sdt."' AND star_add_date <= '".$cfg_edt."' "
                 ." AND stars_add_type in ('SUBJECT_TEST','TUTOR_CHALLENGE','MODULE_TEST')"
                 ." GROUP BY student_id ORDER BY all_stars DESC, total_time ASC"; 
        $data = DB::select($sql);       
        return $data; 
    } 
    
      
    public function getPrizeDataPagination( $subj_id, $cfg_sdt, $cfg_edt, $grade_id, $curr_id, $cur_page, $page_size ) {       
        $st_limit  = 0;
        $end_limit = $page_size;
        if( $cur_page > 1 ) {       
            $st_limit  = ( $cur_page - 1) * $page_size;
            $end_limit = $page_size;
        }        
        
        $cfg_sdt = $cfg_sdt." 08:00:00"; 
        $cfg_edt = $cfg_edt." 20:00:00";              
        if( strtoupper($subj_id) == "ALL" ) {
             $sqlpart = "";  
        } else {
             $sqlpart = " AND subject_id= ".$subj_id;
        }
        $sql = "SELECT *, student_id, SUM(TIME_TO_SEC(duration)) AS total_time, "
                 ." SUM(time_bonus + stars) as all_stars FROM student_stars "   
                 ." WHERE curriculum_id = ".$curr_id." AND grade_id = ".$grade_id.$sqlpart
                 ." AND star_add_date >='".$cfg_sdt."' AND star_add_date <= '".$cfg_edt."' "
                 ." AND stars_add_type in ('SUBJECT_TEST','TUTOR_CHALLENGE','MODULE_TEST')"
                 ." GROUP BY student_id ORDER BY all_stars desc, total_time asc "
                . " LIMIT ".$st_limit.",".$end_limit;        
     
        $data = DB::select($sql);       
        return $data; 
    }
    
    
    public function getCalcPrizeDataPagination( $config_id, $cur_page, $page_size ) {
        $st_limit  = 0;
        $end_limit = $page_size;
        if( $cur_page > 1 ) {       
            $st_limit  = ( $cur_page - 1) * $page_size;
            $end_limit = $page_size;
        } 
        $sql = "SELECT student_id, total_time_taken AS total_time, rank,"   
               ."all_stars FROM student_prizes WHERE prize_conf_id = "
               .$config_id." ORDER BY rank ASC LIMIT ".$st_limit.",".$end_limit;  
        $data = DB::select($sql);       
        return $data; 
    }
    
       
    public function getCalcPrizeDataCount( $config_id ) {
        $sql = "SELECT student_id, total_time_taken AS total_time, rank,"   
               ."all_stars FROM student_prizes WHERE prize_conf_id = "
               .$config_id;  
        $data = DB::select($sql);       
        return count($data);
    }
        
    
    public function getPrizeDataCount( $subj_id, $cfg_sdt, $cfg_edt, $grade_id, $curr_id ) { 
       $cfg_sdt = $cfg_sdt." 08:00:00"; 
       $cfg_edt = $cfg_edt." 20:00:00";        
      if( strtoupper($subj_id) == "ALL" ) {
          $sqlpart = "";  
      } else {
          $sqlpart = " AND s.subject_id= ".$subj_id;
      }
       $sql = "SELECT s.* "   
              ." FROM student_stars s, users u, students st WHERE s.curriculum_id = ".$curr_id
              ." AND s.grade_id = ".$grade_id.$sqlpart." AND s.star_add_date >='".$cfg_sdt              
              ."' AND s.student_id = st.std_id AND st.user_id = u.id AND u.status = '1' "
              ." AND s.star_add_date <= '".$cfg_edt."' "
              ." AND s.stars_add_type in ('SUBJECT_TEST','TUTOR_CHALLENGE','MODULE_TEST')"
              ." GROUP BY s.student_id";
        $data = DB::select($sql);       
        return count($data); 
    }    
    
    
    public function getPrizeConfigOngoingBySubject($subj_id, $curr_id, $grade_id ){
       //DB::enableQueryLog(); 
        $curr_date = Carbon::now()->toDateString();
        if( strtoupper($subj_id) == "ALL" ) {
            $data = DB::table('star_prize_config')                
              ->select('*')   
              ->where('curriculum_id', $curr_id)
              ->where('grade_id', $grade_id)                
              ->where('prize_type', "GRADE_PRIZE")
              ->where('status', "ACTIVE") 
              ->where('price_start_date', "<=", $curr_date) 
              ->where('price_end_date', ">=", $curr_date)
              ->get();
        } else {
            $data = DB::table('star_prize_config')                
              ->select('*')   
              ->where('curriculum_id', $curr_id)
              ->where('grade_id', $grade_id) 
              ->where('subject_id', $subj_id) 
              ->where('prize_type', "SUBJECT_PRIZE")
              ->where('status', "ACTIVE") 
              ->where('price_start_date', "<=", $curr_date) 
              ->where('price_end_date', ">=", $curr_date)
              ->get(); 
        } 
        //var_dump(DB::getQueryLog());
        return $data;
    }
    
    
    public function getPrizeConfigPrevBySubject($subj_id, $curr_id, $grade_id ) {
       $curr_date = Carbon::now()->toDateTimeString(); 
        if( strtoupper($subj_id) == "ALL" ) {
            $data = DB::table('star_prize_config')                
              ->select('*')   
              ->where('curriculum_id', $curr_id)
              ->where('grade_id', $grade_id)             
              ->where('prize_type', "GRADE_PRIZE")
              
              ->where('status', "ACTIVE")             
              ->where('price_end_date', "<=", $curr_date)
              ->get();
        } else {
            $data = DB::table('star_prize_config')                
              ->select('*')   
              ->where('curriculum_id', $curr_id)
              ->where('grade_id', $grade_id) 
              ->where('subject_id', $subj_id) 
              ->where('prize_type', "SUBJECT_PRIZE")
              ->where('status', "ACTIVE")             
              ->where('price_end_date', "<=", $curr_date)
              ->get();
        }       
       
        return $data;
    }    
    
    
    public function getStudentsPrizeData($grade_id, $curr_id, $subj_id ) {       
        if( $subj_id == "all" ) {
            $sqlpart = "";
            $fltrArr = array("SUBJECT_PRIZE", "GRADE_PRIZE" );                     
        } else {
            $fltrArr = array("SUBJECT_PRIZE");
            $sqlpart = " AND c.subject_id=".$subj_id; 
        }        
        $sqlpart.= " AND c.prize_type IN ('".implode("' ,'", $fltrArr)."')";
        $sql = "SELECT p.*, SUM(TIME_TO_SEC(total_time_taken)) AS total_time, "
                   ." SUM((all_stars)) as all_stars "
                   ." FROM student_prizes p, star_prize_config c "
                   ." WHERE c.id = p.prize_conf_id AND "
                   ." p.curriculum_id = ".$curr_id
                   ." AND p.grade_id = ".$grade_id.$sqlpart." GROUP BY p.student_id "
                   ." ORDER BY all_stars desc, total_time asc"; 
        $data = DB::select($sql);
        //print_r($data);
        return $data;        
    }    
    
    
    public function getAllModuleTestResults($stud_id, $curi_id, $grade_id, $subject_id) {        
        $data = DB::table('quiz')                
               ->select('*')                  
               ->where('student_id', $stud_id) 
               ->where('subject_id', $subject_id) 
               ->where('grade_id', $grade_id)
               ->where('curriculum_id', $curi_id)
               ->where('type', "MODULE_TEST") 
               ->where('status', "COMPLETED") 
               ->get(); 
        return $data;
    } 
            
    
    public function getAllModulesBySubject($subject_id) {
         $data = DB::table('quiz')                
               ->select('*')                  
               ->where('student_id', $stud_id) 
               ->where('subject_id', $subject_id) 
               ->where('grade_id', $grade_id)
               ->where('curriculum_id', $curum_id)
               ->where('type', "MODULE_TEST") 
               ->where('status', "COMPLETED") 
               ->get(); 
    }    
    
    
    public function checkAssesmentSession($stu_id, $token, $subj_id ) {
        $data =  DB::table('learning_session_temp')                
               ->select('*')                  
               ->where('student_id', $stu_id) 
               ->where('token', $token) 
               ->where('subject_id', $subj_id)
               ->where('origin', "AZ")
               ->where('status', "NEW") 
               ->get(); 
        return $data;         
    }
    
    
    public function checkLearningSession($stu_id, $token, $subj_id, $module_id, $unit_id ) {
        $data =  DB::table('learning_session_temp')                
               ->select('*')                  
               ->where('student_id', $stu_id) 
               ->where('token', $token) 
               ->where('subject_id', $subj_id)
               ->where('module_id', $module_id)                   
               ->where('unit_id', $unit_id) 
               ->where('origin', "LZ")
               ->where('status', "NEW") 
               ->get(); 
        return $data;         
    } 
            
    
    public function insertLearningSession($data) {
        $res = DB::table("learning_session_temp")->insert($data);
        return $res; 
    }
    
    
    public function updateEndAtLearningSession($stud_id, $token, $unit_id, $end_at) {         
        $upd = array();               
        $upd["end_at"] = $end_at;
        $upd["status"] = "COMPLETED";
        $status = DB::table('learning_session_temp')
                    ->where('student_id', $stud_id)
                    ->where('token', $token)
                    ->where('unit_id', $unit_id)
                    ->where('origin', "LZ" )
                    ->where('status', "NEW")
                    ->update($upd);
        return $status;
    }
    
    
    public function updateEndAtAssesmentSession($stud_id, $token, $subj_id, $end_at) {         
        $upd = array();               
        $upd["end_at"] = $end_at;
        $upd["status"] = "COMPLETED";
        $status = DB::table('learning_session_temp')
                    ->where('student_id', $stud_id)
                    ->where('token', $token)
                    ->where('subject_id', $subj_id)
                    ->where('origin', "AZ" )
                    ->where('status', "NEW")
                    ->update($upd);
        return $status;
    }
    
    
    public function filterTimeOnPortalByDateRange($stud_id, $st_date, $ed_date, $cur_id, $grade_id) {
        $sql = "SELECT * FROM learning_session_temp WHERE curriculum_id = '".$cur_id
               ."' AND grade_id = '".$grade_id."'"
               ."  AND end_at != '0000-00-00 00:00:00' AND student_id = '".$stud_id
               ."' AND ( (begin_at between '".$st_date."' and '".$ed_date."') "
               ."  OR (end_at between '".$st_date."' and '".$ed_date."') )";
        $data = DB::select($sql);
        return $data;
    }          
 
    
    public function getProcessedTimeOnPortal($stud_id, $st_date, $ed_date, $cur_id, $grade_id) {        
       $sql = "SELECT id, subject_id, time_on_portal, on_portal_secs, time_on_portal_date
               FROM learning_session WHERE grade_id = ".$grade_id
               ." AND student_id = ".$stud_id." AND curriculum_id = ".$cur_id
               ." AND time_on_portal_date >= '".$st_date
               ."' AND time_on_portal_date <= '".$ed_date."' order by time_on_portal_date asc";
        $data = DB::select($sql);
        return $data;
    } 
           
         
    public function getStudentsSessionData($ecd) {
        $data = DB::table('learning_session_temp')                
                ->select('*')                
                ->where('end_at',  "<=", $ecd) 
                ->where('end_at',  "!=", "0000-00-00 00:00:00") 
                ->where('begin_at',"!=", "0000-00-00 00:00:00") 
                ->get(); 
        return $data;  
    }
  
    
    public function getOverallOnPortalByStudentId($stud_id, $cur_id, $grade_id ){
        //DB::enableQueryLog(); 
        $sql = "SELECT * FROM learning_session_temp WHERE curriculum_id = '".$cur_id
               ."' AND grade_id = ".$grade_id." AND student_id = "
               .$stud_id." AND status='COMPLETED'";               
        $data = DB::select($sql);
        return $data;
       // var_dump(DB::getQueryLog());
    }             
       
    
    public function insertPortalSpendTime($cur_id, $grade_id, $stud_id, $subj_id, $prt_time, $prt_date ) {
        $is_avail = DB::table('learning_session')                
                    ->select('*')                
                    ->where('student_id', $stud_id) 
                    ->where('curriculum_id', $cur_id) 
                    ->where('grade_id',  $grade_id) 
                    ->where('subject_id',  $subj_id) 
                    ->where('time_on_portal_date',  $prt_date) 
                    ->get(); 
        if( !$is_avail) {
            $insertArr = array();
            $insertArr["student_id"]     = $stud_id;
            $insertArr["curriculum_id"]  = $cur_id;
            $insertArr["grade_id"]       = $grade_id;
            $insertArr["subject_id"]     = $subj_id;
            $insertArr["time_on_portal"] = $this->secToHR($prt_time);
            $insertArr["on_portal_secs"] = $prt_time;
            $insertArr["time_on_portal_date"] = $prt_date;
            
            $reslt = DB::table("learning_session")->insert($insertArr);
        } else {
            $updateArr = array();
            $updateArr["on_portal_secs"] = $prt_time + $is_avail[0]->on_portal_secs;
            $updateArr["time_on_portal"] = $this->secToHR( $updateArr["on_portal_secs"]);
           
            $reslt = DB::table('learning_session')
                     ->where('student_id',   $stud_id)
                     ->where('curriculum_id',$cur_id)                    
                     ->where('grade_id',     $grade_id)
                     ->where('subject_id',   $subj_id)                    
                     ->where('time_on_portal_date', $prt_date)
                     ->update( $updateArr );            
        }
    }
    
    
    public function deleteProcessedRec($tmp_rec_id ) {
        DB::table('learning_session_temp')->where('id',$tmp_rec_id)->delete(); 
    }
    
    
    public function secToHR($seconds) {
        $hours   = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        
        return "$hours:$minutes:$seconds";
    }
    
    
    public function getLeaderBoardData($grade_id, $curr_id, $subj_id ) {          
        if( $subj_id == "all") {
            $sqlpart = "";
        } else {
            $sqlpart = " AND subject_id=".$subj_id;
           /// $sqlpart = " AND subject_id=".$subj_id." AND stars_add_type='SUBJECT_TEST' ";
        }
        $sqlpart .= " AND stars_add_type in ('SUBJECT_TEST','TUTOR_CHALLENGE','MODULE_TEST','SECOND_FORM_FILL_UP') ";
        $sql = "SELECT student_id, SUM(TIME_TO_SEC(duration)) AS total_time, "
               ." SUM((time_bonus + stars)) as all_stars, stars_add_type "               
               ." FROM student_stars  "
               ." WHERE curriculum_id = ".$curr_id." AND grade_id = ".$grade_id.$sqlpart
               ." GROUP BY  student_id HAVING all_stars > 0 ORDER BY all_stars desc, total_time asc";        
        $data = DB::select($sql);
        
        return $data;        
    }

    
    public function is_activeStudent($stu_id) {
        $data = DB::table('students as s')
              ->join('users as u', 'u.id', '=', 's.user_id')            
              ->select('s.std_id' )           
              ->where('s.std_id', $stu_id)
              ->where('u.status', "1")
              ->get();
        return $data;
    }
    
    
    public function getStudentsDataByIds($stu_id) {
       $data = DB::table('students as s')
              ->join('users as u', 'u.id', '=', 's.user_id')            
              ->select('s.std_id','s.knid', 's.full_name','u.file_id', 's.user_id' )
             // ->select('s.std_id', 's.full_name','u.file_id', 's.user_id' )
              ->where('s.std_id', $stu_id)
              ->where('u.status', "1")
              ->get();
        $row = array();
        if($data) {
            $row["user_id"]    = $data[0]->user_id;
            $row["file_id"]    = $data[0]->file_id;
            $row["full_name"]  = $data[0]->full_name;
            $row["student_id"] = $data[0]->std_id;
            $row["knid"]       = $data[0]->knid;
        } else {
            $row["user_id"]    = "";
            $row["file_id"]    = "";
            $row["full_name"]  = "";
            $row["student_id"] = $stu_id;
            $row["knid"]       = "";
        }
        return $row;      
    }
    
    
    public function getProfileByKnid( $knid ) {
        $data = DB::table('students as s')
              ->join('users as u', 'u.id', '=', 's.user_id')            
              ->select('s.*','u.file_id', 'u.email' )
              ->where('s.knid', $knid)
              ->get();       
        return $data;
    }
        
    
    public function getLeaderboardHistory($stud_id, $subj_id, $grade_id, $curr_id, $ttypes, $lmt ) {  
        $qry = " AND stars_add_type in ('".implode( "','", $ttypes )."')";
        if( $subj_id != "all") 
            $qry .= " AND subject_id = ".$subj_id;        
        $sql = "SELECT * FROM student_stars WHERE student_id =".$stud_id
                ." AND grade_id = ".$grade_id." AND "
                ." curriculum_id = ".$curr_id." AND (stars + time_bonus) > 0"
                .$qry." ORDER BY star_add_date DESC LIMIT ".$lmt;
        $data = DB::select($sql);        
        /*
        if( $subj_id == "all") {
            $data = DB::table('student_stars')->select('*') 
                    ->where('student_id', $stud_id)               
                    ->where('grade_id', $grade_id)
                    ->where('curriculum_id', $curr_id) 
                    ->whereIn('stars_add_type', $ttypes ) 
                    ->limit($lmt)->orderBy("star_add_date", "desc")->get();
        } else {
            $data = DB::table('student_stars')->select('*') 
                    ->where('student_id', $stud_id)               
                    ->where('grade_id', $grade_id)
                    ->where('curriculum_id', $curr_id) 
                    ->where('subject_id', $subj_id)
                    ->whereIn('stars_add_type', $ttypes ) 
                    ->limit($lmt)->orderBy("star_add_date", "desc")->get();
        }
      */
        return $data;       
     }
     
     
    public function getLastResults( $stud_id, $subj_id, $grade_id, $curr_id, $result_count ) {
         //DB::enableQueryLog();
          $data = DB::table('student_stars')                
                  ->select('full_marks', 'stars', 'time_bonus', 'duration')                  
                  ->where('student_id', $stud_id) 
                  ->where('curriculum_id', $curr_id) 
                  ->where('grade_id', $grade_id) 
                  ->where('subject_id', $subj_id)
                  ->where('student_id', $stud_id) 
                  ->whereIn('stars_add_type', ["MODULE_TEST", "SUBJECT_TEST"])
                  ->limit($result_count)
                  ->orderBy('id', 'desc')
                  ->get(); 
          //var_dump(DB::getQueryLog());
          return $data;
     }
     
	
    public function getlModuleTestResultByMId( $stud_id, $curi_id, $grade_id, $subject_id, $mid ) {
         $data = DB::table('quiz')                
               ->select('*')                  
               ->where('student_id', $stud_id) 
               ->where('subject_id', $subject_id) 
               ->where('grade_id', $grade_id)
               ->where('curriculum_id', $curi_id)
               ->where('module_id', $mid)
               ->where('type', "MODULE_TEST") 
               ->where('status', "COMPLETED") 
               ->limit($this->sw_test_count)
               ->orderBy('id', 'desc')
               ->get(); 
        return $data;
     }
     
     
    public function checkPrizeConfigOngoing( $curr_id, $grade_id ){    
        // DB::enableQueryLog();
        $curr_date = Carbon::now()->toDateString();        
        $data = DB::table('star_prize_config')                
              ->select('*')   
              ->where('curriculum_id', $curr_id)
              ->where('grade_id', $grade_id) 
              ->where('status', "ACTIVE") 
              ->where('price_start_date', "<=", $curr_date) 
              ->where('price_end_date', ">=", $curr_date)
              ->get(); 
        //var_dump(DB::getQueryLog());
        return $data;
    }
     
     
    public function getStudentDetailsById($stu_id) {    
       $data = DB::table('students as s')
              ->join('users as u', 'u.id', '=', 's.user_id')            
              ->select('s.*','u.file_id', 's.user_id' )
              ->where('s.std_id', $stu_id)
              ->get();
        $row = array();
        if($data) {
            $row["user_id"]    = $data[0]->user_id;
            $row["file_id"]    = $data[0]->file_id;
            $row["full_name"]  = $data[0]->full_name;
            $row["student_id"] = $data[0]->std_id;            
            $row["reference"]  = $data[0]->reference;
            $row["gender"]     = $data[0]->gender;            
            $row["school"]     = $data[0]->school;
            $row["school_city"] = $data[0]->school_city;
        }
        return $row;      
    }
    
    
    public function getPrivPrizeConfig( $subj_id, $curr_id, $grade_id ) { 
        $curr_date = Carbon::now()->toDateString(); 
        if( strtoupper($subj_id) == "ALL" ) {
            $data = DB::table('star_prize_config')                
              ->select('*')   
              ->where('curriculum_id', $curr_id)
              ->where('grade_id', $grade_id)             
              ->where('prize_type', "GRADE_PRIZE")
              ->whereIn('status', $this->prize_conf_status )       
             // ->where('status', "ACTIVE")             
              ->where('price_end_date', "<", $curr_date)
              ->orderBy('price_end_date', 'desc')
              ->get();
        } else {
            $data = DB::table('star_prize_config')                
              ->select('*')   
              ->where('curriculum_id', $curr_id)
              ->where('grade_id', $grade_id) 
              ->where('subject_id', $subj_id) 
              ->where('prize_type', "SUBJECT_PRIZE")
              ->whereIn('status', $this->prize_conf_status ) 
             // ->where('status', "ACTIVE")             
              ->where('price_end_date', "<", $curr_date)
              ->orderBy('price_end_date', 'desc')
              ->get();
        }         
        return $data;
    }
    
    
    public function checkOngoingGradePzConfig( $curr_id, $grade_id ) { 
        $curr_date = Carbon::now()->toDateString();        
        $data = DB::table('star_prize_config')                
              ->select('*')   
              ->where('curriculum_id', $curr_id)
              ->where('grade_id', $grade_id) 
              ->where('status', "ACTIVE") 
              ->where('prize_type', "GRADE_PRIZE")                 
              ->where('price_start_date', "<=", $curr_date) 
              ->where('price_end_date', ">=", $curr_date)
              ->first();         
        return $data;
    }
    
    
    public function checkOngoingSubjectPzConfig( $subject_id, $curr_id, $grade_id ) { 
        $curr_date = Carbon::now()->toDateString();   
        //DB::enableQueryLog(); 
        $data = DB::table('star_prize_config')                
              ->select('*') 
              ->where('subject_id', $subject_id)
              ->where('curriculum_id', $curr_id)
              ->where('grade_id', $grade_id) 
              ->where('status', "ACTIVE") 
              ->where('prize_type', "SUBJECT_PRIZE")                 
              ->where('price_start_date', "<=", $curr_date) 
              ->where('price_end_date', ">=", $curr_date)
              ->first();
        //var_dump(DB::getQueryLog());
        return $data;
    }
    
    
    public function getAllSubjects() {    
       $data = DB::table('subject')->get();      
        return $data;      
    }
    
    
    public function getPrivGradePrizeConfig( $curr_id, $grade_id ) { 
        $curr_date = Carbon::now()->toDateString();        
        $data = DB::table('star_prize_config')                
                ->select('*')   
                ->where('curriculum_id', $curr_id)
                ->where('grade_id', $grade_id)             
                ->where('prize_type', "GRADE_PRIZE")
                //->where('status', "ACTIVE")  
                ->whereIn('status', $this->prize_conf_status ) 
                ->where('price_end_date', "<", $curr_date)
                ->orderBy('price_end_date', 'desc')
                ->first();
        return $data;
    }
            
   
    public function getPrivSubjectPrizeConfig( $subj_id, $curr_id, $grade_id ) { 
        $curr_date = Carbon::now()->toDateString();   
        $data = DB::table('star_prize_config')                
              ->select('*')   
              ->where('curriculum_id', $curr_id)
              ->where('grade_id', $grade_id) 
              ->where('subject_id', $subj_id) 
              ->where('prize_type', "SUBJECT_PRIZE")
              ->whereIn('status', $this->prize_conf_status ) 
              //->where('status', "ACTIVE")             
              ->where('price_end_date', "<", $curr_date)
              ->orderBy('price_end_date', 'desc')
              ->first();
        return $data;   
    }        
  
  
    /*
     public function getAssesmentHistoryData($stud_id, $grade_id, $curr_id, $lmt ) {
         //DB::enableQueryLog();         
        $data = DB::table('quiz')                
               ->select('*')                  
               ->where('student_id', $stud_id)               
               ->where('grade_id', $grade_id)
               ->where('curriculum_id', $curr_id)             
               ->where('status', "COMPLETED") 
               ->limit($lmt)
               ->orderBy("created_date", "desc")
               ->get(); 
        return $data;
        //var_dump(DB::getQueryLog());
     }
      */   
     
     
     
             
}
