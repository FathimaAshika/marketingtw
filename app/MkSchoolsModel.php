<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class MkSchoolsModel extends Model {
       
    
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table      = 'mrk_schools';
    protected $fillable   = [ "school_name",   "school_type",  "curriculum",
                              "primary_intake", "secondary_intake", "no_of_students",
                              "no_of_subcribers", "grade_level", "member_of",
                              "school_gender", "status",  "created_date", 
                              "created_by",       "last_mod_date",    "last_mod_by",                                                     
                            ];
    
    
    public function getSummaryCount($filter) {
        return DB::table('mrk_schools')->where($filter)->count(); 
    }
    
    
    
    public function getSummary( $filter, $page_limit, $st_limit, $ordbcol, $ordby ) {  
        //DB::enableQueryLog();        
        $data = DB::table('mrk_schools')
                    ->where($filter)
                    ->orderBy($ordbcol, $ordby)
                    ->offset($st_limit)
                    ->limit($page_limit)
                    ->get(); 
      // var_dump(DB::getQueryLog());
       return $data;        
    }
    
    public function checkSchool( $sch_name, $sch_id ) {
        //DB::enableQueryLog();   
        if($sch_id) {
            $count = DB::table('mrk_schools')
                   ->where("school_name", $sch_name)
                   ->where("status", "ACTIVE")
                   ->where("id", "<>", $sch_id)                    
                   ->count();
        }  else {
            $count = DB::table('mrk_schools')
                   ->where("school_name", $sch_name)
                   ->where("status", "ACTIVE")                                     
                   ->count();
        } 
        return $count;        
    }
    
    
    public function getCountSubscribers( $school_name) {
        $count = DB::table('students')
                 ->where("school", $school_name)                                  
                 ->count();
         return $count;
    }
    
}
