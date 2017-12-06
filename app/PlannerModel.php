<?php
namespace App;

use DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;


class PlannerModel extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'planner_id';
    protected $table      = 'planner';
    protected $fillable  = [
                        "username",
                        "user_type",
                        "status",
                        "first_name",
                        "password",
                    ]; 
    
    public function getRemindPlansForToday() {
        //DB::enableQueryLog(); 
       $dt   = Carbon::now()->toDateString();
       $data = DB::table('planner_reminder as r')                
               ->join('planner as p', 'p.id', '=', 'r.planner_id')
               ->select( 'p.id', 'p.std_id', 'p.title', 'p.start_date', 'p.end_date', 'p.event_color',
                      'p.start_time', 'end_time', 'r.reminder_date', 'r.reminder_time' )                 
               ->where('r.reminder_date', $dt)  
               ->where('p.end_date', '>=', $dt) 
               ->where('p.is_reminded', '0') 
               ->get();
       //var_dump(DB::getQueryLog());
        return $data;
    }
   
    
    
     
}
