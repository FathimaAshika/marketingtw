<?php
namespace App;
use DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;


class LZoneModel extends Model {
    
    protected $connection = 'mysql';
    
    
    public function getAllHeadings() {
        $res  = array();
        $data = DB::table('headings')->select("*")->get();
        foreach( $data as $v ) 
            $res[$v->id] = $v->name;
        
        return $res;
    }
    
    
    public function getAllStarFeedbackReasons() {
        $data = array();
        $res  = DB::table('star_rating')
               ->select("*")
               ->get();
        foreach( $res as $v ) {
           $data[$v->id] = $v->name;
        }
        return $data;
    }
    
    
    public function getWorksheetResAvailByUnitId($unit_id, $grade_id, $curr_id ) {
        $res = DB::table('question')
               ->select("*")
               ->where('unit_id', $unit_id)
               ->where('grade_id', $grade_id)
               ->where('curriculum_id', $curr_id)
               ->where('status', "APPROVED")
               ->count(); 
        return $res;
    }

    
    public function getChllengeSavedAnswer($ans_id) {
        $res = DB::table('tutor_challange_answer')
               ->select("*")
               ->where('id', $ans_id)
               ->get();               
        return $res;
    }  
    
    
    public function incrementStudentCount($question_id) {
        $res = DB::table('tutor_challange_question')
               ->where('id', $question_id)
               ->update(['student_count'=>DB::raw('student_count + 1')]);
        return $res;
    }
    
    
    public function insertTutorChallengeAnswer( $ans_id, $ans_arr ) {
         $res = DB::table("tutor_challange_answer")->insert($ans_arr);
         return $res;
    } 
    
    
    public function updateTutorChallengeAnswer( $ans_id, $ans_data ) {         
        $res = DB::table('tutor_challange_answer')
               ->where('id', $ans_id)
               ->update($ans_data);
        return $res;
    }
    
        
    public function checkValidQuestion( $unit_id, $question_id ) {
         $data = DB::table('tutor_challange_question') 
                ->select('*')  
                ->where('id',  $question_id)
                ->where('unit_id', $unit_id)
                ->where('status', "ACTIVE")                 
                ->get();
         return $data;
    }
    
        
    public function getTutorChallengeResorcesByQId($tcq_id) {
        $i = 0; 
        $final_arr = array(); 
        $data = DB::table('tutor_challange_resource') 
                ->select('*')  
                ->where('question_id',  $tcq_id)                               
                ->get();
        if($data) {
            foreach( $data as $v ) {
               $final_arr[$i]["id"] = $v->id;                
               $final_arr[$i]["resource_type_id"] = $v->resource_type_id;                
               $final_arr[$i]["fileID"] = $v->fileID;                
               $final_arr[$i]["res_name"] = $v->name; 
               
               if($v->resource_type_id) 
                   $res_typ = DB::table('resource_types')->select('*')
                              ->where('id', $v->resource_type_id)->get();               
               $final_arr[$i]["type_name"] = isset($res_typ[0]->name) ? $res_typ[0]->name : ""; 
               $final_arr[$i]["icon"]      = isset($res_typ[0]->icon)  ? $res_typ[0]->icon  : "";
               $i++;
            }  
        }
        return $final_arr;
        /*
         $data = DB::table('tutor_challange_resource as tr')
              ->join('resource_types as ty', 'ty.id', '=', 'tr.resource_type_id')            
              ->select('tr.id', 'tr.resource_type_id', 'tr.fileID',
                      'tr.name as res_name','ty.name as type_name', 'ty.icon')
              ->where('tr.question_id', $tcq_id)
              ->get();
        */
        // return $data;
    }
	
      
    public function getTutorChallengeAnswerByQId( $tcq_id, $student_id ) { 
         //DB::enableQueryLog();   
        $data = DB::table('tutor_challange_answer') 
                ->select('*')  
                ->where('student_id',  $student_id)
                ->where('question_id',  $tcq_id)
                ->get(); 
         //var_dump(DB::getQueryLog());
        return $data;
    }
       
    
    public function getActiveTutorChallengeData($unit_id) {       
        $curr_date = Carbon::now()->toDateString(); 
        $sql = "SELECT * FROM tutor_challange_question WHERE "
                ."unit_id = ".$unit_id." AND start_date <= '".$curr_date
                ."' AND end_date >= '".$curr_date."' AND student_limit >= student_count "
                ."AND admin_confirmation = 1 AND status = 'ACTIVE' ORDER BY created_date DESC";                    
       return collect(\DB::select($sql))->first();    
    }
    
        
    public function getTutorChallResTypeId() { 
        $data = DB::table('resource_types') 
                ->select('id')  
                ->where('name', 'LIKE', '%challeng%')
                ->get();        
        return isset($data[0]->id) ? $data[0]->id :0;
    }
    
    
    public function getTypeIdByText($text) { 
        $data = DB::table('resource_types') 
                ->select('id')  
                ->where('name', 'LIKE', '%'."$text".'%')
                ->get();        
        return isset($data[0]->id) ? $data[0]->id :0;
    }
    
    public function getTutorChallResAvailByUnitId($unitId) {
        $curr_date = Carbon::now()->toDateString(); 
        $data   = DB::table('tutor_challange_question') 
                ->select('id')  
                ->where('unit_id', $unitId )
                ->where('status', 'ACTIVE')
                ->where('end_date', '>=', $curr_date )
                ->where('start_date', '<=', $curr_date )
                ->get();
        return $data;
    }
    
    
     public function getFaqSearchResult( $unit_id, $word ) {  
        $data = DB::table('unit_faqs') 
                ->select('id', 'faq', 'faq_answer', 'hits')  
                ->where('unit_id', $unit_id)
                ->where('faq', 'LIKE', '%'."$word".'%')
                ->get(); 
        return $data; 
     }
     
     
     public function saveStarFeedbackReason($stud_id, $tc_answer_id, $reason, $rating, $feedback ) {
          $upd = array();               
          $upd["stars_reason"]   = $reason;
          $upd["star_rating"]    = $rating;
          $upd["rating_feedback"]= $feedback;
          
          $st = DB::table('tutor_challange_answer')
                ->where('student_id', $stud_id )
                ->where('id', $tc_answer_id )  
                ->update( $upd );
          return $st;
     }
     
     
    public function getFirstUnitByModule( $mid ) {          
        $sql = "SELECT id FROM unit WHERE module_id = ".$mid." ORDER BY id ASC";                    
        return collect(\DB::select($sql))->first();
    }
    
    
    public function getTutorChallByQuestion_id( $qid ) {          
       return  DB::table('tutor_challange_question') 
                ->select("*")  
                ->where('id', $qid)
                ->where('status', 'ACTIVE')
                ->get();  
    }
    
}



    
    
    
    
 
   
  
    