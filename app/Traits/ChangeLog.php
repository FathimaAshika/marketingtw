<?php
namespace App\Traits;
use App;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;


trait ChangeLog {
    
    
    public function prepairChangeLog($sch_id, $new_data, $old_data, $change_cols ) {
        $changa_data = array();
        $i = 0;
        foreach( $change_cols as $col ) {
            if( isset($new_data[$col]) && isset($old_data->$col) && $new_data[$col] != $old_data->$col ) {
                $changa_data[$i]["field_name"]     = $col;	
                $changa_data[$i]["current_value"]  = $new_data[$col];	
                $changa_data[$i]["previous_value"] = $old_data->$col;
                $i++;
            }
        }        
        return $changa_data;
    }
    
    
    public function insertChangeLog( $entity_id, $entity_type, $edit_type, $user_id,  $changed_data) {
        $header = array();
        $header["entity_id"]    = $entity_id;	
        $header["entity_type"]  = $entity_type;        
        $header["edit_type"]    = $edit_type;        
        $header["created_by"]   = $user_id;
	$header["created_date"] = Carbon::now()->toDateTimeString(); 		
	$clog_id = DB::table("mrk_change_log")->insertGetId($header);         
        if( $clog_id ) {
            foreach( $changed_data as $val ) {
                $change_line = array();
                $change_line["change_log_id"]  = $clog_id;                
                $change_line["field_name"]     = $val["field_name"];                
                $change_line["current_value"]  = $val["current_value"];
                $change_line["previous_value"] = $val["previous_value"]; 
                DB::table("mrk_change_log_line")->insert($change_line); 
            }            
        }  
        return $clog_id;
    }
  
    
    public function deleteChangeLog( $entity_id, $entity_type ) {
        if( $entity_id && $entity_type ) {
            $clog_ids = DB::table('mrk_change_log')
                        ->where('entity_id', $entity_id)
                        ->where('entity_type', $entity_type)
                        ->pluck("id");            
            $d1 = DB::table('mrk_change_log_line')->whereIn('change_log_id', $clog_ids)->delete();
            if($d1)
                $res = DB::table('mrk_change_log')->whereIn('id', $clog_ids)->delete();       
            
            return $res;
        }    
    }
}