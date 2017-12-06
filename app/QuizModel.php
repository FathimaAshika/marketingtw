<?php

namespace App;
use DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;


class QuizModel extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table      = 'quiz';
    private   $test_count = 10;
    protected $fillable  = [ 
                        "id", "student_id", "subject_id",
                        "module_id", "type", "questions",
                        "schedule_time", "time_taken", "speed_bonus",
                        "start_time", "end_time", "stars",
                        "quiz_name", "full_marks", "status",
                        "created_date", "last_mod_date"        
                        ];    
 
    public function getSubmitedQuizLinesByQuizId($quiz_id) {         
       // DB::enableQueryLog();  
        $data =  DB::table('quiz_question as qq')                
                ->join('question as q', 'q.id', '=', 'qq.question_id')
                ->select('qq.*', 'q.question_type_id' ) 
                ->where('qq.quiz_id', $quiz_id)                
                ->orderBy('qq.question_order', 'asc')                  
                ->get();  
        // var_dump(DB::getQueryLog());         
        return $data;
    }
    
     
    public function getStudentScoresBySubjectId($stu_id, $subject_id, $grade_id, $curr_id) {
        //DB::enableQueryLog(); 
        $data =  DB::table('quiz')                
                 ->select('id', 'subject_id', 'time_taken', 'speed_bonus',
                         'stars', 'quiz_name', 'full_marks', 'created_date' ) 
                 ->where('student_id', $stu_id)
                 ->where('subject_id', $subject_id)
                 ->where('curriculum_id', $curr_id)
                 ->where('grade_id', $grade_id)
                 ->where('type', "SUBJECT_TEST")
                 ->where('status', "COMPLETED")
                 ->limit($this->test_count)
                 ->orderBy('id', 'desc')                  
                 ->get();
       // var_dump(DB::getQueryLog());    
        return $data;  
    }
    
    
    public function getSubjectById($sbid) {
        return DB::table('subject')->select('*')->where("id",$sbid)->get(); 
    }
    
    
    public function getModuleTestResults($stu_id, $subj_id, $module_id, $grade_id, $curr_id ) {
       $data =  DB::table('quiz')                
                 ->select('id', 'subject_id', 'time_taken', 'speed_bonus',
                         'stars', 'quiz_name', 'full_marks', 'created_date' ) 
                 ->where('student_id', $stu_id)
                 ->where('subject_id', $subj_id)
                 ->where('module_id', $module_id)
                 ->where('curriculum_id', $curr_id)
                 ->where('grade_id', $grade_id)
                 ->where('type', "MODULE_TEST")
                 ->where('status', "COMPLETED")
                 ->limit($this->test_count)
                 ->orderBy('id', 'desc')                  
                 ->get();
        return $data;
    } 
    
    
    public function getModuleById($mid) {
        return DB::table('module')->select('*')->where("id",$mid)->get(); 
    }
    
     public function getAllSubjectScoresByStudentId($stu_id, $grade_id, $curr_id) {
        $data =  DB::table('quiz')                
                 ->select('id', 'subject_id', 'time_taken', 'speed_bonus',
                         'stars', 'quiz_name', 'full_marks', 'created_date' ) 
                 ->where('student_id', $stu_id)
                 ->where('status', "COMPLETED")
                 ->where('type', "SUBJECT_TEST")
                 ->where('curriculum_id', $curr_id)
                 ->where('grade_id', $grade_id)
                 ->orderBy('id', 'asc')                  
                 ->get();
        return $data;  
    }
    
    
    
     public function getAllSubjectsDetails() {
        $subjs = array();
        $data =  DB::table('subject')->select('*')->get(); 
        foreach( $data as $k=>$v) {
            $subjs[$v->id]["subject_id"]  = $v->id;
            $subjs[$v->id]["color"]       = $v->color;
            $subjs[$v->id]["subject_name"]= $v->name;
        } 
        return $subjs;
    }
    
    
    public function getDoneModuleQids($stud_id, $subj_id, $module_id, $grade_id, $curr_id ) {
        $res = array();
        $data =  DB::table('quiz_question as qq')                
                ->join('quiz as q', 'q.id', '=', 'qq.quiz_id')
                ->select( 'qq.question_id' )                   
                ->where('q.student_id', $stud_id)                
                ->where('q.module_id', $module_id)                                 
                ->where('q.grade_id', $grade_id) 
                ->where('q.curriculum_id', $curr_id)                
                ->get();
        if($data) {
           foreach(  $data as $d ) 
              $res[$d->question_id] = $d->question_id;
        }        
       return $res;
    }
    
    
    public function getDoneQids($stud_id, $subj_id, $grade_id, $curr_id ) {
        $res = array();
        $data =  DB::table('quiz_question as qq')                
                ->join('quiz as q', 'q.id', '=', 'qq.quiz_id')
                ->select( 'qq.question_id' )                   
                ->where('q.student_id', $stud_id)                
                ->where('q.subject_id', $subj_id)                                 
                ->where('q.grade_id', $grade_id) 
                ->where('q.curriculum_id', $curr_id)                
                ->get();
        if($data) {
           foreach(  $data as $d ) 
              $res[$d->question_id] = $d->question_id;
        }        
       return $res;
    }
    
    
    public function getUncompletedTestData($student_id, $grade_id, $curr_id) {       
       $fdate = Carbon::now()->subDays(1)->toDateTimeString();    
       //DB::enableQueryLog();  
       $data =  DB::table('quiz') 
                 ->select('id', 'subject_id','module_id','type', 
                         'schedule_time', 'start_time', 'end_time' )                  
                 ->where('student_id', $student_id)
                 ->where('status', "NEW")                 
                 ->where('curriculum_id', $curr_id)
                 ->where('grade_id', $grade_id) 
                 ->where('start_time', '>', $fdate)
                 ->get();
       //var_dump(DB::getQueryLog()); 
        return $data;        
    }
                         
   
    public function getModuleQuestionCount($module_id, $subj_id, $grade_id, $curr_id) { 
       // DB::enableQueryLog();  
       $mq_count = DB::table('question') 
                  ->select('id')   
                  ->where('module_id', $module_id)
                  ->where('subject_id', $subj_id)
                  ->where('grade_id', $grade_id)
                  ->where('curriculum_id', $curr_id)
                  ->where('status', "APPROVED") 
                  ->count();
      // var_dump(DB::getQueryLog());                   
       return $mq_count;
    }  
    
    
     public function getSubjectQuestionCount( $subj_id, $grade_id, $curr_id) {
         $sq_count = DB::table('question') 
                   ->select('id') 
                   ->where('subject_id', $subj_id)
                   ->where('grade_id', $grade_id)
                   ->where('curriculum_id', $curr_id)
                   ->where('status', "APPROVED") 
                   ->count();
         return $sq_count;
     }
     
     
     
     public function getQuestion($qid) { 
         //DB::enableQueryLog();  
        $data =  DB::table('question as q')                
                ->join('ques_section_mapping as m', 'q.id', '=', 'm.question_id')
                ->select('m.main_quest_id', 'm.section_id', 'q.*' ) 
                ->where('q.id', $qid) 
                ->get(); 
        //var_dump(DB::getQueryLog());  
        return $data;
     }
     
     
     public function getAnswersByQuestionId($qid) {
         $data = DB::table('answer')  
                 ->select('*' ) 
                 ->where('question_id', $qid) 
                 ->get(); 
         return $data;
     }
     
     
    public function getModuleTestResultsByModuleId( $stu_id, $module_id, $grade_id, $currum_id ) { 
        $rslt = DB::table('student_stars')                           
               ->select('quiz_id', 'star_add_date as test_date', 'stars' , 'full_marks', 'time_bonus', 'duration as time_taken' )
               ->where('module_id', $module_id)  
               ->where('grade_id', $grade_id) 
               ->where('student_id', $stu_id)
               ->where('curriculum_id', $currum_id) 
               ->where('stars_add_type', "MODULE_TEST") 
               ->orderBy('id', 'desc')->get(); 
        return $rslt;
    } 
     
    
    public function getDoneWorksheetQids($stud_id, $unit_id ) {
        $res  = array();
        $data = DB::table('worksheet_student_map')                           
               ->select('question_id' )
               ->where('student_id', $stud_id) 
               ->where('unit_id',    $unit_id)  
               ->orderBy('popularity', 'desc')
               ->get(); 
        
        if($data) {
           foreach(  $data as $d ) 
              $res[$d->question_id] = $d->question_id;
        }        
        
        return $res;
    }
    
}
