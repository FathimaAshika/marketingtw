<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Student;
use Session;
use Carbon\Carbon;
use App\PlannerModel;



class remindPlanner extends Command  {
    
    
    protected $signature   = 'remind:planner_notif';
    protected $description = 'Command description';
    
      
    public function __construct( Student $student_model, PlannerModel $planner_model ) {
        parent::__construct();
        $this->student_model = new $student_model; 
        $this->planner_model = new $planner_model;        
    }
    
    
    public function fl_comment($msg) {
       echo  "\n$msg\n";      
    }
        
   
    public function handle() { 
        $remind_data = $this->planner_model->getRemindPlansForToday(); 
        if($remind_data) {
            foreach( $remind_data as $pln ) {
                $stu_id = $pln->std_id; 
                $student_data = DB::table('students')
                                ->where('std_id', $stu_id)
                                ->get();
                $notif_msg = "Reminder: ".$pln->title;
                $c_date    = Carbon::now()->toDateTimeString();
                $link      = "No Link";
                $pl_color  = !empty($pln->event_color) ? ("#".$pln->event_color) : "";            
                
                $log_msg = $pln->title." reminded to ".$student_data[0]->firstName."(Ref:".$student_data[0]->reference.")";
                $this->fl_comment($log_msg);                
                
                $notif = array();
                $notif["notification"] = $notif_msg;
                $notif["link"]         = $link;
                $notif["subject_id"]   = 0;
                $notif["created_date"] = $c_date; 
                $notif["notif_color"]  = $pl_color;
                $notif["package_id"]   = isset($student_data[0]->package_id) ? $student_data[0]->package_id : 0;
                $notif_id = DB::table("group_notification")->insertGetId($notif);
        
                if($notif_id) {
                   $notif_user = array();
                   $notif_user["status"]  = "0";
                   $notif_user["user_id"] = $student_data[0]->user_id;
                   $notif_user["notification_id"] = $notif_id;                   
                   $user_notif_id = DB::table("group_notification_user")->insertGetId($notif_user);
                   
                   $upd = array();               
                   $upd["is_reminded"] = "1"; 
                   DB::table('planner')->where('id', $pln->id)->update($upd);
                   //$rslt = DB::statement('CALL spInsertNotification("'.$student_data[0]->user_id.'", "'.$link.'", @feedID, "'.$c_date.'")');
                   //$status  =  DB::select("select @feedID as fid");
                }
            }
        } else {
           $this->fl_comment("No Data");  
        }        
    }
}
