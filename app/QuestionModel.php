<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class QuestionModel extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'question';
    protected $fillable  = [ 	 	  	 	 	 	 	 	
                        "id",
                        "description",
                        "question",
                        "created_date",
                        "grade_id",
                        "curriculum_id",
                        "unit_id",
                        "subject_id",        
                        "module_id",
                        "unit_id",
                        "subject_id",
                    ];
    
    
    public function getQuestionsByUnitId( $unit_id, $subj_id, $grade_id, $curr_id ) { 
      $data = DB::table($this->table) 
               ->select('id', 'question_type_id')
               ->where('subject_id', $subj_id)
               ->where('grade_id', $grade_id)
               ->where('curriculum_id', $curr_id)
               ->where('unit_id', $unit_id)
               ->where('status', "APPROVED") 
               ->orderBy('popularity', "ASC")                          
               ->get();       
        return $data;
    }
    
}
