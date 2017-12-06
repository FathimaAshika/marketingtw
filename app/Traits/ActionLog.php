<?php
namespace App\Traits;
use App;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;


trait ActionLog {
    
    
    public function insertActionLog( $entity_id, $type, $c_status, $p_status, $user_id ) {       
        $alog = array();
        $alog["entity_id"]    = $entity_id;
        $alog["entity_type"]  = $type;
        $alog["curr_status"]  = $c_status;
        $alog["priv_status"]  = $p_status; 
        $alog["created_date"] = Carbon::now()->toDateTimeString();
        $alog["created_by"]   = $user_id;
        DB::table("mrk_action_log")->insert($alog); 
    }
    
 
}
