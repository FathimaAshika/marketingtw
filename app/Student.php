<?php
namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;



class Student extends Model {
    
    public    $timestamps = false;
    
    protected $connection = 'mysql';
    protected $primaryKey = 'std_id';
    protected $table      = 'students';
    protected $fillable   = ["reference",
                            "std_id",
                            "user_id",
                            "full_name",
                            "firstName",
                            "familyName",
                            "gender",
                            "dob",
                            "nation",
                            "address1",
                            "address2",
                            "city",
                            "country",
                            "grade",
                            "school",
                            "intake_month",
                            "school_type",
                            "found_through",
                            "package_id",
                            "school_city",
                            "crm_type",
                            "note",
                            "category",
                            "subscr_type",
                            "knid"
                            ]; 
           
 
    public function getBirthDayList() {
        $dt    = Carbon::now(); 
        $day   = $dt->day;
        $month = $dt->month;        
        $fltr = $day."/".$month."/";       
        //DB::enableQueryLog();
        $data = DB::table('students as s')  
                ->join('users as u', 'u.id','=','s.user_id') 
                ->select('s.*' )
                ->where('u.status', "1")
                ->where('s.dob', 'LIKE', $fltr."%")              
                ->get();         
        //var_dump(DB::getQueryLog());
        return $data;         
    }
        
  
    public function getStudentList() {
         $data = DB::table('students as s')  
                ->join('users as u', 'u.id','=','s.user_id') 
                ->select('u.email', 's.reference', 's.std_id', 's.user_id', 's.full_name', 's.firstName'
                         , 's.familyName', 's.gender', 's.dob', 's.nation', 's.country', 's.package_id' )
                ->where('u.status', "1")                         
                ->get(); 
         return $data;
    }


    
    

}
